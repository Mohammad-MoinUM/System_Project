import React, { useEffect, useRef, useState } from 'react';
import { ActivityIndicator, Alert, SafeAreaView, ScrollView, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { Feather } from '@expo/vector-icons';
import MapView, { Marker } from 'react-native-maps';
import {
  confirmCustomerCompletion,
  fetchLiveTrackingStatus,
  requestProviderCompletion,
} from '../services/api';
import { usePlaceName } from '../hooks/usePlaceName';
import { useProviderTracking } from '../hooks/useProviderTracking';

const geocodeCache = new Map();

async function geocodeLocationQuery(query) {
  const normalizedQuery = String(query || '').trim();

  if (!normalizedQuery) {
    return null;
  }

  if (geocodeCache.has(normalizedQuery)) {
    return geocodeCache.get(normalizedQuery);
  }

  const response = await fetch(
    `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(normalizedQuery)}`,
    {
      headers: {
        Accept: 'application/json',
        'Accept-Language': 'en',
      },
    }
  );

  const results = await response.json().catch(() => []);
  const firstResult = Array.isArray(results) && results.length > 0 ? results[0] : null;

  if (!firstResult) {
    geocodeCache.set(normalizedQuery, null);
    return null;
  }

  const location = {
    latitude: Number(firstResult.lat),
    longitude: Number(firstResult.lon),
    label: firstResult.display_name || normalizedQuery,
  };

  geocodeCache.set(normalizedQuery, location);
  return location;
}

export default function TrackingScreen({ session, theme }) {
  const [state, setState] = useState({ loading: true, error: '', payload: null });
  const [actionLoading, setActionLoading] = useState(false);
  const [serviceLocation, setServiceLocation] = useState(null);
  const [serviceLocationLoading, setServiceLocationLoading] = useState(false);
  const mapRef = useRef(null);
  const role = session?.user?.role || 'customer';
  const screenTheme = theme || {
    background: '#090b10',
    card: '#101826',
    text: '#f8fafc',
    muted: '#d1d5db',
    border: '#1f2937',
    accent: '#4ade80',
    accentSoft: 'rgba(74, 222, 128, 0.16)',
  };
  const { placeName: providerPlaceName, loading: providerPlaceLoading } = usePlaceName(
    state.payload?.provider_latitude,
    state.payload?.provider_longitude
  );

  const loadTracking = async () => {
    try {
      const payload = await fetchLiveTrackingStatus(session?.token);
      setState({ loading: false, error: '', payload });
    } catch (error) {
      setState({ loading: false, error: error.message || 'Failed to load tracking data.', payload: null });
    }
  };

  useEffect(() => {
    loadTracking().catch(() => {});
  }, [session?.token]);

  useEffect(() => {
    if (role !== 'customer') {
      return undefined;
    }

    const interval = setInterval(() => {
      loadTracking().catch(() => {});
    }, 3000);

    return () => clearInterval(interval);
  }, [role, session?.token]);

  useEffect(() => {
    if (role !== 'customer') {
      setServiceLocation(null);
      return undefined;
    }

    const query = state.payload?.service_location_query;

    if (!query) {
      setServiceLocation(null);
      return undefined;
    }

    let cancelled = false;

    const resolveServiceLocation = async () => {
      try {
        setServiceLocationLoading(true);
        const resolved = await geocodeLocationQuery(query);

        if (!cancelled) {
          setServiceLocation(resolved);
        }
      } catch {
        if (!cancelled) {
          setServiceLocation(null);
        }
      } finally {
        if (!cancelled) {
          setServiceLocationLoading(false);
        }
      }
    };

    resolveServiceLocation().catch(() => {});

    return () => {
      cancelled = true;
    };
  }, [role, state.payload?.service_location_query]);

  useEffect(() => {
    if (role !== 'customer') {
      return;
    }

    const providerLatitude = Number(state.payload?.provider_latitude);
    const providerLongitude = Number(state.payload?.provider_longitude);

    if (!Number.isFinite(providerLatitude) || !Number.isFinite(providerLongitude)) {
      return;
    }

    if (serviceLocation?.latitude && serviceLocation?.longitude && mapRef.current?.fitToCoordinates) {
      mapRef.current.fitToCoordinates(
        [
          {
            latitude: serviceLocation.latitude,
            longitude: serviceLocation.longitude,
          },
          {
            latitude: providerLatitude,
            longitude: providerLongitude,
          },
        ],
        {
          edgePadding: { top: 90, right: 50, bottom: 90, left: 50 },
          animated: true,
        }
      );
      return;
    }

    mapRef.current?.animateToRegion(
      {
        latitude: providerLatitude,
        longitude: providerLongitude,
        latitudeDelta: 0.02,
        longitudeDelta: 0.02,
      },
      800
    );
  }, [role, state.payload?.provider_latitude, state.payload?.provider_longitude, serviceLocation?.latitude, serviceLocation?.longitude]);

  const activeBookingId = state.payload?.active_booking_id;
  const activeStatus = state.payload?.active_booking_status;
  const canProviderRequest = role === 'provider' && state.payload?.can_request_completion && !!activeBookingId;
  const canCustomerConfirm = role === 'customer' && state.payload?.can_confirm_completion && !!activeBookingId;

  useProviderTracking(session?.token, activeBookingId, role === 'provider' && !!activeBookingId);

  const handleProviderRequestCompletion = async () => {
    if (!activeBookingId || actionLoading) return;

    try {
      setActionLoading(true);
      const response = await requestProviderCompletion(session?.token, activeBookingId);
      Alert.alert('Completion request', response?.message || 'Completion request sent.');
      await loadTracking();
    } catch (error) {
      Alert.alert('Request failed', error.message || 'Unable to send completion request.');
    } finally {
      setActionLoading(false);
    }
  };

  const handleCustomerConfirmCompletion = async () => {
    if (!activeBookingId || actionLoading) return;

    try {
      setActionLoading(true);
      const response = await confirmCustomerCompletion(session?.token, activeBookingId);
      Alert.alert('Service confirmed', response?.message || 'Service completion confirmed.');
      await loadTracking();
    } catch (error) {
      Alert.alert('Confirmation failed', error.message || 'Unable to confirm completion.');
    } finally {
      setActionLoading(false);
    }
  };

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: screenTheme.background }]}>
      <View style={styles.glowBottom} />
      <ScrollView contentContainerStyle={styles.container}>
        <View style={[styles.heroCard, { backgroundColor: screenTheme.card, borderColor: screenTheme.border }]}>
          <View style={styles.heroTop}>
            <View style={styles.heroIconWrap}>
              <Feather name="navigation" size={16} color={screenTheme.background} />
            </View>
            <Text style={[styles.heroTag, { color: screenTheme.accent }]}>{role}</Text>
          </View>

          <Text style={[styles.title, { color: screenTheme.text }]}>Live Tracking</Text>
          <Text style={[styles.text, { color: screenTheme.muted }]}>Location updates for active jobs and booking visibility.</Text>
        </View>

        {state.loading ? (
          <View style={styles.loadingBox}>
            <ActivityIndicator color={screenTheme.accent} />
            <Text style={[styles.loadingText, { color: screenTheme.muted }]}>Loading tracking status...</Text>
          </View>
        ) : null}

        {state.error ? <Text style={styles.error}>{state.error}</Text> : null}

        <View style={[styles.mapCard, { backgroundColor: screenTheme.card, borderColor: screenTheme.border }]}>
          <Text style={[styles.mapTitle, { color: screenTheme.text }]}>Tracking payload</Text>
          {activeBookingId ? (
            <View style={[styles.statusBox, { backgroundColor: screenTheme.background, borderColor: screenTheme.border }]}>
              <Text style={[styles.statusLabel, { color: screenTheme.muted }]}>Active booking #{String(activeBookingId)}</Text>
              <Text style={[styles.statusValue, { color: screenTheme.text }]}>{String(activeStatus || 'unknown').replaceAll('_', ' ')}</Text>
            </View>
          ) : null}
          {role === 'customer' && state.payload?.provider_latitude && state.payload?.provider_longitude ? (
            <View style={styles.customerMapWrap}>
              <View style={styles.customerMapHeader}>
                <Text style={[styles.customerMapTitle, { color: screenTheme.text }]}>Provider on the way</Text>
                <Text style={[styles.customerMapSubtitle, { color: screenTheme.muted }]}>Updated every 3 seconds</Text>
              </View>

              <View style={styles.customerMapContainer}>
                <MapView
                  ref={mapRef}
                  style={styles.customerMap}
                  initialRegion={{
                    latitude: Number(serviceLocation?.latitude || state.payload.provider_latitude),
                    longitude: Number(serviceLocation?.longitude || state.payload.provider_longitude),
                    latitudeDelta: 0.02,
                    longitudeDelta: 0.02,
                  }}
                >
                  {serviceLocation?.latitude && serviceLocation?.longitude ? (
                    <Marker
                      coordinate={{
                        latitude: serviceLocation.latitude,
                        longitude: serviceLocation.longitude,
                      }}
                      title="Your service location"
                      pinColor="blue"
                    />
                  ) : null}

                  <Marker
                    coordinate={{
                      latitude: Number(state.payload.provider_latitude),
                      longitude: Number(state.payload.provider_longitude),
                    }}
                    title="Service provider"
                  >
                    <Text style={{ fontSize: 30 }}>🛵</Text>
                  </Marker>
                </MapView>
              </View>

              <View style={styles.customerMapLegend}>
                <View style={styles.legendRow}>
                  <View style={[styles.legendDot, styles.legendDotBlue]} />
                  <Text style={styles.legendText}>Your service location</Text>
                </View>
                <View style={styles.legendRow}>
                  <Text style={styles.legendEmoji}>🛵</Text>
                  <Text style={styles.legendText}>Live provider position</Text>
                </View>
                  {serviceLocationLoading ? (
                  <View style={styles.legendRow}>
                    <ActivityIndicator size="small" color={screenTheme.accent} />
                    <Text style={[styles.legendText, { color: screenTheme.muted }]}>Resolving service address...</Text>
                  </View>
                ) : null}
              </View>
            </View>
          ) : state.payload?.provider_latitude && state.payload?.provider_longitude ? (
            <View style={styles.placeBox}>
              <Text style={styles.statusLabel}>Provider location</Text>
              {providerPlaceLoading ? (
                <ActivityIndicator size="small" color={screenTheme.accent} style={styles.placeLoader} />
              ) : null}
                <Text style={[styles.placeValue, { color: screenTheme.text }]}>
                {providerPlaceName?.short || `${state.payload.provider_latitude}, ${state.payload.provider_longitude}`}
              </Text>
              <Text style={[styles.placeCoords, { color: screenTheme.muted }]}>
                {state.payload.provider_latitude}, {state.payload.provider_longitude}
              </Text>
            </View>
          ) : null}
          {state.payload ? (
            Object.entries(state.payload).map(([key, value]) => (
              <View key={key} style={styles.dataRow}>
                <Text style={styles.dataKey}>{key.replaceAll('_', ' ')}</Text>
                <Text style={styles.dataValue}>{String(value ?? 'N/A')}</Text>
              </View>
            ))
          ) : (
            <Text style={styles.mapText}>No tracking payload.</Text>
          )}
        </View>

        {canProviderRequest ? (
          <TouchableOpacity
            style={[styles.actionButton, actionLoading && styles.actionButtonDisabled]}
            onPress={handleProviderRequestCompletion}
            disabled={actionLoading}
          >
            <Text style={styles.actionButtonText}>
              {actionLoading ? 'Sending request...' : 'Send Completion Request'}
            </Text>
          </TouchableOpacity>
        ) : null}

        {canCustomerConfirm ? (
          <TouchableOpacity
            style={[styles.actionButton, styles.confirmButton, actionLoading && styles.actionButtonDisabled]}
            onPress={handleCustomerConfirmCompletion}
            disabled={actionLoading}
          >
            <Text style={styles.actionButtonText}>
              {actionLoading ? 'Confirming...' : 'Confirm Service Completed'}
            </Text>
          </TouchableOpacity>
        ) : null}

        <View style={[styles.tipCard, { backgroundColor: screenTheme.accentSoft, borderColor: screenTheme.border }]}>
          <Text style={[styles.tipTitle, { color: screenTheme.text }]}>Why mobile here?</Text>
          <Text style={[styles.tipText, { color: screenTheme.text }]}>Live location and movement monitoring are faster and more useful on Android devices.</Text>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1, backgroundColor: '#090b10' },
  glowBottom: {
    position: 'absolute',
    bottom: -160,
    left: -110,
    width: 300,
    height: 300,
    borderRadius: 150,
    backgroundColor: 'rgba(74, 222, 128, 0.16)',
  },
  container: { padding: 20, paddingBottom: 36 },
  heroCard: {
    backgroundColor: '#101826',
    borderRadius: 24,
    padding: 18,
    borderWidth: 1,
    borderColor: '#1f2937',
    marginBottom: 14,
  },
  heroTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10 },
  heroIconWrap: {
    width: 36,
    height: 36,
    borderRadius: 10,
    backgroundColor: '#4ade80',
    alignItems: 'center',
    justifyContent: 'center',
  },
  heroTag: {
    color: '#86efac',
    fontWeight: '800',
    textTransform: 'uppercase',
    letterSpacing: 0.8,
    fontSize: 11,
  },
  title: { fontSize: 30, fontWeight: '900', color: '#f8fafc', marginBottom: 6 },
  text: { fontSize: 14, lineHeight: 21, color: '#d1d5db' },
  loadingBox: { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 10 },
  loadingText: { color: '#bbf7d0' },
  error: { color: '#fca5a5', marginBottom: 10, fontWeight: '700' },
  mapCard: { borderRadius: 22, backgroundColor: '#dcfce7', borderWidth: 1, borderColor: '#bbf7d0', padding: 18 },
  mapTitle: { fontSize: 20, fontWeight: '800', color: '#14532d', marginBottom: 10 },
  statusBox: {
    borderWidth: 1,
    borderColor: '#86efac',
    borderRadius: 12,
    backgroundColor: '#f0fdf4',
    padding: 10,
    marginBottom: 10,
  },
  statusLabel: { color: '#166534', fontSize: 12, fontWeight: '700' },
  statusValue: {
    color: '#14532d',
    fontSize: 16,
    fontWeight: '900',
    textTransform: 'capitalize',
    marginTop: 2,
  },
  placeBox: {
    borderWidth: 1,
    borderColor: '#86efac',
    borderRadius: 12,
    backgroundColor: '#ecfdf5',
    padding: 10,
    marginBottom: 10,
  },
  placeLoader: { marginTop: 6 },
  placeValue: { color: '#14532d', fontSize: 15, fontWeight: '800', marginTop: 4 },
  placeCoords: { color: '#166534', fontSize: 12, marginTop: 2 },
  customerMapWrap: {
    borderWidth: 1,
    borderColor: '#86efac',
    borderRadius: 16,
    backgroundColor: '#f0fdf4',
    padding: 10,
    marginBottom: 10,
  },
  customerMapHeader: { marginBottom: 10 },
  customerMapTitle: { color: '#14532d', fontSize: 15, fontWeight: '900' },
  customerMapSubtitle: { color: '#166534', fontSize: 12, marginTop: 2 },
  customerMapContainer: {
    overflow: 'hidden',
    borderRadius: 14,
    borderWidth: 1,
    borderColor: '#bbf7d0',
    height: 290,
    backgroundColor: '#dff7e8',
  },
  customerMap: { flex: 1 },
  customerMapLegend: { marginTop: 10, gap: 8 },
  legendRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  legendDot: { width: 10, height: 10, borderRadius: 5 },
  legendDotBlue: { backgroundColor: '#2563eb' },
  legendEmoji: { fontSize: 16 },
  legendText: { color: '#166534', fontSize: 12, fontWeight: '700' },
  mapText: { fontSize: 14, color: '#166534', marginBottom: 4 },
  dataRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#bbf7d0',
  },
  dataKey: { color: '#166534', fontSize: 14, textTransform: 'capitalize' },
  dataValue: { color: '#14532d', fontSize: 14, fontWeight: '800' },
  actionButton: {
    marginTop: 12,
    backgroundColor: '#14532d',
    borderRadius: 14,
    paddingVertical: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  confirmButton: {
    backgroundColor: '#0f766e',
  },
  actionButtonDisabled: {
    opacity: 0.7,
  },
  actionButtonText: {
    color: '#ffffff',
    fontWeight: '800',
    fontSize: 14,
    letterSpacing: 0.2,
  },
  tipCard: {
    marginTop: 12,
    backgroundColor: '#f0fdf4',
    borderWidth: 1,
    borderColor: '#bbf7d0',
    borderRadius: 16,
    padding: 14,
  },
  tipTitle: { fontSize: 15, fontWeight: '800', color: '#14532d', marginBottom: 4 },
  tipText: { fontSize: 13, lineHeight: 19, color: '#166534' },
});
