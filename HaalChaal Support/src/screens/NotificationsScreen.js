import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Switch,
  Text,
  View,
} from 'react-native';
import { Feather } from '@expo/vector-icons';
import { fetchLiveTrackingStatus, fetchPaymentStatus, fetchSupportOverview } from '../services/api';

function AlertCard({ icon, title, value, note, theme }) {
  return (
    <View style={[styles.alertCard, { backgroundColor: theme.card, borderColor: theme.border }]}>
      <View style={[styles.alertIcon, { backgroundColor: theme.accentSoft }]}>
        <Feather name={icon} size={16} color={theme.accent} />
      </View>
      <Text style={[styles.alertTitle, { color: theme.text }]}>{title}</Text>
      <Text style={[styles.alertValue, { color: theme.accent }]}>{String(value)}</Text>
      <Text style={[styles.alertNote, { color: theme.muted }]}>{note}</Text>
    </View>
  );
}

export default function NotificationsScreen({ session, theme }) {
  const [overview, setOverview] = useState(null);
  const [payments, setPayments] = useState(null);
  const [tracking, setTracking] = useState(null);
  const [loading, setLoading] = useState(true);
  const [appAlerts, setAppAlerts] = useState(true);
  const [bookingAlerts, setBookingAlerts] = useState(true);
  const [paymentAlerts, setPaymentAlerts] = useState(true);

  useEffect(() => {
    let active = true;

    Promise.all([
      fetchSupportOverview(session?.token),
      fetchPaymentStatus(session?.token),
      fetchLiveTrackingStatus(session?.token),
    ])
      .then(([overviewPayload, paymentsPayload, trackingPayload]) => {
        if (!active) {
          return;
        }

        setOverview(overviewPayload);
        setPayments(paymentsPayload);
        setTracking(trackingPayload);
        setLoading(false);
      })
      .catch(() => {
        if (!active) {
          return;
        }

        setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [session?.token]);

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: theme.background }]}>
      <ScrollView contentContainerStyle={styles.container} showsVerticalScrollIndicator={false}>
        <View style={[styles.heroCard, { backgroundColor: theme.card, borderColor: theme.border }]}>
          <View style={[styles.heroIcon, { backgroundColor: theme.accent }]}>
            <Feather name="bell" size={18} color="#ffffff" />
          </View>
          <View style={styles.heroTextWrap}>
            <Text style={[styles.heroTitle, { color: theme.text }]}>Notifications</Text>
            <Text style={[styles.heroSubtitle, { color: theme.muted }]}>Follow chat, payment, and booking updates from one place.</Text>
          </View>
        </View>

        {loading ? (
          <View style={styles.loadingRow}>
            <ActivityIndicator color={theme.accent} />
            <Text style={[styles.loadingText, { color: theme.muted }]}>Loading alert summary...</Text>
          </View>
        ) : null}

        <View style={styles.grid}>
          <AlertCard
            icon="message-circle"
            title="Unread booking chats"
            value={overview?.unread_booking_chats ?? 0}
            note="Messages waiting in booking threads."
            theme={theme}
          />
          <AlertCard
            icon="credit-card"
            title="Payments"
            value={payments?.pending_payment ?? 0}
            note="Bookings waiting on payment updates."
            theme={theme}
          />
          <AlertCard
            icon="navigation"
            title="Live jobs"
            value={tracking?.active_jobs ?? 0}
            note="Bookings currently in progress."
            theme={theme}
          />
        </View>

        <View style={[styles.sectionCard, { backgroundColor: theme.card, borderColor: theme.border }]}>
          <Text style={[styles.sectionTitle, { color: theme.text }]}>Alert preferences</Text>
          <View style={styles.settingRow}>
            <View style={styles.settingTextWrap}>
              <Text style={[styles.settingLabel, { color: theme.text }]}>App alerts</Text>
              <Text style={[styles.settingNote, { color: theme.muted }]}>General banner and badge notifications.</Text>
            </View>
            <Switch
              value={appAlerts}
              onValueChange={setAppAlerts}
              trackColor={{ false: '#334155', true: theme.accentSoft }}
              thumbColor={appAlerts ? theme.accent : '#cbd5e1'}
            />
          </View>
          <View style={styles.settingRow}>
            <View style={styles.settingTextWrap}>
              <Text style={[styles.settingLabel, { color: theme.text }]}>Booking updates</Text>
              <Text style={[styles.settingNote, { color: theme.muted }]}>Notify when a booking chat needs attention.</Text>
            </View>
            <Switch
              value={bookingAlerts}
              onValueChange={setBookingAlerts}
              trackColor={{ false: '#334155', true: theme.accentSoft }}
              thumbColor={bookingAlerts ? theme.accent : '#cbd5e1'}
            />
          </View>
          <View style={styles.settingRow}>
            <View style={styles.settingTextWrap}>
              <Text style={[styles.settingLabel, { color: theme.text }]}>Payment reminders</Text>
              <Text style={[styles.settingNote, { color: theme.muted }]}>Highlight bookings that still need payment.
              </Text>
            </View>
            <Switch
              value={paymentAlerts}
              onValueChange={setPaymentAlerts}
              trackColor={{ false: '#334155', true: theme.accentSoft }}
              thumbColor={paymentAlerts ? theme.accent : '#cbd5e1'}
            />
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1 },
  container: { padding: 20, gap: 14 },
  heroCard: {
    borderWidth: 1,
    borderRadius: 24,
    padding: 18,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
  },
  heroIcon: {
    width: 52,
    height: 52,
    borderRadius: 18,
    alignItems: 'center',
    justifyContent: 'center',
  },
  heroTextWrap: { flex: 1 },
  heroTitle: { fontSize: 26, fontWeight: '900', marginBottom: 4 },
  heroSubtitle: { fontSize: 13, lineHeight: 19 },
  loadingRow: { flexDirection: 'row', alignItems: 'center', gap: 10 },
  loadingText: { fontSize: 13 },
  grid: { flexDirection: 'row', gap: 10, flexWrap: 'wrap' },
  alertCard: {
    width: '31%',
    minWidth: 96,
    flexGrow: 1,
    borderWidth: 1,
    borderRadius: 20,
    padding: 14,
    alignItems: 'flex-start',
    gap: 8,
  },
  alertIcon: { width: 32, height: 32, borderRadius: 10, alignItems: 'center', justifyContent: 'center' },
  alertTitle: { fontSize: 12, fontWeight: '800' },
  alertValue: { fontSize: 26, fontWeight: '900' },
  alertNote: { fontSize: 11.5, lineHeight: 16 },
  sectionCard: {
    borderWidth: 1,
    borderRadius: 22,
    padding: 16,
    gap: 12,
  },
  sectionTitle: { fontSize: 18, fontWeight: '800' },
  settingRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 12 },
  settingTextWrap: { flex: 1 },
  settingLabel: { fontSize: 15, fontWeight: '800', marginBottom: 2 },
  settingNote: { fontSize: 12.5, lineHeight: 18 },
});
