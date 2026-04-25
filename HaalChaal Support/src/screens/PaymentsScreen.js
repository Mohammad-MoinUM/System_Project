import React, { useEffect, useState } from 'react';
import { ActivityIndicator, SafeAreaView, ScrollView, StyleSheet, Text, View } from 'react-native';
import { Feather } from '@expo/vector-icons';
import { fetchPaymentStatus } from '../services/api';

export default function PaymentsScreen({ session, theme }) {
  const [state, setState] = useState({ loading: true, error: '', payload: null });
  const role = session?.user?.role || 'customer';
  const screenTheme = theme || {
    background: '#090b10',
    card: '#101826',
    text: '#f8fafc',
    muted: '#d1d5db',
    border: '#1f2937',
    accent: '#7dd3fc',
    accentSoft: 'rgba(125, 211, 252, 0.15)',
  };

  useEffect(() => {
    let active = true;

    fetchPaymentStatus(session?.token)
      .then((payload) => {
        if (active) {
          setState({ loading: false, error: '', payload });
        }
      })
      .catch((error) => {
        if (active) {
          setState({ loading: false, error: error.message || 'Failed to load payment data.', payload: null });
        }
      });

    return () => {
      active = false;
    };
  }, [session?.token]);

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: screenTheme.background }]}>
      <View style={styles.glowTop} />
      <ScrollView contentContainerStyle={styles.container}>
        <View style={[styles.heroCard, { backgroundColor: screenTheme.card, borderColor: screenTheme.border }]}>
          <View style={styles.heroTop}>
            <View style={styles.heroIconWrap}>
              <Feather name="credit-card" size={16} color={screenTheme.background} />
            </View>
            <Text style={[styles.heroTag, { color: screenTheme.accent }]}>{role}</Text>
          </View>

          <Text style={[styles.title, { color: screenTheme.text }]}>Payments</Text>
          <Text style={[styles.text, { color: screenTheme.muted }]}>Fast payment visibility for support decisions on mobile.</Text>
        </View>

        {state.loading ? (
          <View style={styles.loadingBox}>
            <ActivityIndicator color={screenTheme.accent} />
            <Text style={[styles.loadingText, { color: screenTheme.muted }]}>Loading payment stats...</Text>
          </View>
        ) : null}

        {state.error ? <Text style={styles.error}>{state.error}</Text> : null}

        <View style={[styles.card, { backgroundColor: screenTheme.card, borderColor: screenTheme.border }]}>
          <Text style={[styles.label, { color: screenTheme.text }]}>Live metrics</Text>
          {state.payload ? (
            Object.entries(state.payload).map(([key, value]) => (
              <View key={key} style={styles.metricRow}>
                <Text style={[styles.metricKey, { color: screenTheme.muted }]}>{key.replaceAll('_', ' ')}</Text>
                <Text style={[styles.metricValue, { color: screenTheme.text }]}>{String(value)}</Text>
              </View>
            ))
          ) : (
            <Text style={[styles.item, { color: screenTheme.muted }]}>- No data yet</Text>
          )}
        </View>

        <View style={[styles.tipCard, { backgroundColor: screenTheme.accentSoft, borderColor: screenTheme.border }]}>
          <Text style={[styles.tipTitle, { color: screenTheme.text }]}>Why mobile here?</Text>
          <Text style={[styles.tipText, { color: screenTheme.text }]}>Payment checks on Android are useful for quick confirmation during active service flow.</Text>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1, backgroundColor: '#090b10' },
  glowTop: {
    position: 'absolute',
    top: -120,
    right: -90,
    width: 260,
    height: 260,
    borderRadius: 130,
    backgroundColor: 'rgba(56, 189, 248, 0.15)',
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
    backgroundColor: '#7dd3fc',
    alignItems: 'center',
    justifyContent: 'center',
  },
  heroTag: {
    color: '#bae6fd',
    fontWeight: '800',
    textTransform: 'uppercase',
    letterSpacing: 0.8,
    fontSize: 11,
  },
  title: { fontSize: 30, fontWeight: '900', color: '#f8fafc', marginBottom: 6 },
  text: { fontSize: 14, lineHeight: 21, color: '#d1d5db' },
  loadingBox: { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 10 },
  loadingText: { color: '#e2e8f0' },
  error: { color: '#fca5a5', marginBottom: 10, fontWeight: '700' },
  card: { backgroundColor: '#ffffff', borderRadius: 18, padding: 16, borderWidth: 1, borderColor: '#e2e8f0' },
  label: { fontSize: 16, fontWeight: '800', color: '#0f172a', marginBottom: 10 },
  metricRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  metricKey: { fontSize: 14, color: '#334155', textTransform: 'capitalize' },
  metricValue: { fontSize: 14, color: '#0f172a', fontWeight: '800' },
  item: { fontSize: 14, color: '#334155', marginBottom: 6 },
  tipCard: {
    marginTop: 12,
    backgroundColor: '#ecfeff',
    borderWidth: 1,
    borderColor: '#a5f3fc',
    borderRadius: 16,
    padding: 14,
  },
  tipTitle: { fontSize: 15, fontWeight: '800', color: '#0f172a', marginBottom: 4 },
  tipText: { fontSize: 13, lineHeight: 19, color: '#164e63' },
});
