import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { Feather } from '@expo/vector-icons';
import {
  confirmCustomerCompletion,
  fetchMyBookings,
  payCustomerBooking,
  reportCustomerIssue,
  requestProviderCompletion,
  startProviderBooking,
  tipCustomerBooking,
} from '../services/api';
import { usePlaceName } from '../hooks/usePlaceName';

function ActionButton({ label, onPress, variant = 'primary', disabled, theme }) {
  return (
    <TouchableOpacity
      style={[
        styles.actionButton,
        { backgroundColor: theme.accent },
        variant === 'secondary' && { backgroundColor: theme.card, borderColor: theme.border },
        variant === 'danger' && { backgroundColor: '#ef4444' },
        disabled && styles.actionButtonDisabled,
      ]}
      onPress={onPress}
      disabled={disabled}
    >
      <Text style={[styles.actionButtonText, { color: variant === 'secondary' ? theme.text : '#111827' }]}>{label}</Text>
    </TouchableOpacity>
  );
}

function BookingCard({
  booking,
  role,
  busy,
  tipDraft,
  paymentDraft,
  onTipDraftChange,
  onPaymentDraftChange,
  onPayDue,
  onSendTip,
  onStartJob,
  onRequestCompletion,
  onConfirmCompletion,
  onReportIssue,
  onOpenChat,
  theme,
}) {
  const latitude = booking.latitude ?? booking.provider_latitude;
  const longitude = booking.longitude ?? booking.provider_longitude;
  const { placeName, loading } = usePlaceName(latitude, longitude);

  return (
    <View style={[styles.jobCard, { backgroundColor: theme.card, borderColor: theme.border }]}>
      <View style={styles.jobTopRow}>
        <Text style={styles.jobId}>#{booking.id}</Text>
        <Text style={styles.jobStatus}>{String(booking.status || '').replaceAll('_', ' ')}</Text>
      </View>

      <Text style={styles.jobService}>{booking.service_name || 'Service'}</Text>
      <Text style={styles.jobMeta}>
        Total: BDT {Number(booking.total_with_tips ?? booking.total ?? 0).toFixed(2)}
      </Text>
      {Number(booking.tip_total || 0) > 0 ? (
        <Text style={styles.jobMeta}>Tip: BDT {Number(booking.tip_total || 0).toFixed(2)}</Text>
      ) : null}
      <Text style={styles.jobMeta}>
        Payment: {booking.payment_method || 'n/a'} / {booking.payment_status || 'n/a'}
      </Text>
      {Number(booking.due_amount || 0) > 0 ? (
        <Text style={styles.jobMeta}>Due: BDT {Number(booking.due_amount || 0).toFixed(2)}</Text>
      ) : null}

      {latitude !== null && latitude !== undefined && longitude !== null && longitude !== undefined ? (
        <View style={styles.placePanel}>
          <Text style={styles.placeLabel}>Location</Text>
          {loading ? <ActivityIndicator size="small" color="#86efac" style={styles.placeLoader} /> : null}
          <Text style={styles.placeValue}>{placeName?.short || `${latitude}, ${longitude}`}</Text>
          <Text style={styles.placeCoords}>{latitude}, {longitude}</Text>
        </View>
      ) : null}

      {role === 'customer' && booking.can_tip ? (
        <View style={styles.tipPanel}>
          <Text style={styles.tipLabel}>Send a tip</Text>
          <TextInput
            value={tipDraft.amount}
            onChangeText={(value) => onTipDraftChange(booking.id, 'amount', value)}
            keyboardType="decimal-pad"
            placeholder="Tip amount"
            placeholderTextColor="#94a3b8"
            style={styles.tipInput}
          />

          <View style={styles.methodRow}>
            {['wallet', 'bkash', 'nagad', 'card'].map((method) => {
              const active = tipDraft.method === method;
              return (
                <TouchableOpacity
                  key={method}
                  style={[styles.methodChip, active && styles.methodChipActive]}
                  onPress={() => onTipDraftChange(booking.id, 'method', method)}
                >
                  <Text style={[styles.methodChipText, active && styles.methodChipTextActive]}>
                    {method}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </View>

          <TouchableOpacity
            style={[styles.tipButton, busy && styles.tipButtonDisabled]}
            onPress={() => onSendTip(booking)}
            disabled={busy}
          >
            <Text style={styles.tipButtonText}>{busy ? 'Sending...' : 'Send Tip'}</Text>
          </TouchableOpacity>
        </View>
      ) : null}

      {role === 'customer' && booking.can_pay_due ? (
        <View style={styles.tipPanel}>
          <Text style={styles.tipLabel}>Pay booking due</Text>
          <Text style={styles.payHint}>
            Amount: BDT {Number(booking.due_amount || 0).toFixed(2)}
          </Text>

          <View style={styles.methodRow}>
            {['wallet', 'bkash', 'nagad', 'card'].map((method) => {
              const active = paymentDraft.method === method;
              return (
                <TouchableOpacity
                  key={method}
                  style={[styles.methodChip, active && styles.methodChipActive]}
                  onPress={() => onPaymentDraftChange(booking.id, method)}
                >
                  <Text style={[styles.methodChipText, active && styles.methodChipTextActive]}>
                    {method}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </View>

          <TouchableOpacity
            style={[styles.tipButton, busy && styles.tipButtonDisabled]}
            onPress={() => onPayDue(booking)}
            disabled={busy}
          >
            <Text style={styles.tipButtonText}>{busy ? 'Processing...' : 'Pay Now'}</Text>
          </TouchableOpacity>
        </View>
      ) : null}

      <View style={styles.actionsWrap}>
        {booking.can_start_job ? (
            <ActionButton
            label={busy ? 'Starting...' : 'Start Job'}
            onPress={onStartJob}
            disabled={busy}
              theme={theme}
          />
        ) : null}

        {booking.can_request_completion ? (
            <ActionButton
            label={busy ? 'Sending...' : 'Request Completion'}
            variant="secondary"
            onPress={onRequestCompletion}
            disabled={busy}
              theme={theme}
          />
        ) : null}

        {booking.can_confirm_completion ? (
            <ActionButton
            label={busy ? 'Confirming...' : 'Confirm Completion'}
            onPress={onConfirmCompletion}
            disabled={busy}
              theme={theme}
          />
        ) : null}

        {booking.can_report_issue ? (
            <ActionButton
            label={busy ? 'Reporting...' : 'Report Issue'}
            variant="danger"
            onPress={onReportIssue}
            disabled={busy}
              theme={theme}
          />
        ) : null}

        {booking.can_chat ? (
            <ActionButton
            label={
              Number(booking.unread_booking_chat_count || 0) > 0
                ? `Booking Chat (${booking.unread_booking_chat_count})`
                : 'Booking Chat'
            }
            variant="secondary"
            onPress={onOpenChat}
            disabled={busy}
              theme={theme}
          />
        ) : null}
      </View>
    </View>
  );
}

export default function JobsScreen({ session, navigation, theme }) {
  const screenTheme = theme || {
    background: '#090b10',
    card: '#111827',
    text: '#f8fafc',
    muted: '#94a3b8',
    border: '#1f2937',
    accent: '#86efac',
    accentSoft: 'rgba(134, 239, 172, 0.18)',
  };
  const [state, setState] = useState({ loading: true, error: '', bookings: [] });
  const [busyBookingId, setBusyBookingId] = useState(null);
  const [tipDrafts, setTipDrafts] = useState({});
  const [paymentDrafts, setPaymentDrafts] = useState({});
  const role = session?.user?.role || 'customer';

  const loadBookings = async () => {
    try {
      const payload = await fetchMyBookings(session?.token);
      setState({ loading: false, error: '', bookings: payload?.bookings || [] });
    } catch (error) {
      setState({ loading: false, error: error.message || 'Unable to load bookings.', bookings: [] });
    }
  };

  useEffect(() => {
    loadBookings().catch(() => {});
  }, [session?.token]);

  const executeAction = async (bookingId, action) => {
    try {
      setBusyBookingId(bookingId);
      const response = await action();
      Alert.alert('Success', response?.message || 'Done');
      await loadBookings();
    } catch (error) {
      Alert.alert('Failed', error.message || 'Action failed');
    } finally {
      setBusyBookingId(null);
    }
  };

  const reportIssue = (bookingId) => {
    executeAction(bookingId, () =>
      reportCustomerIssue(session?.token, bookingId, {
        subject: 'Completion dispute from mobile app',
        details:
          'Customer reported that service completion is disputed and requested support review from the mobile app.',
      })
    );
  };

  const updateTipDraft = (bookingId, field, value) => {
    setTipDrafts((current) => ({
      ...current,
      [bookingId]: {
        ...(current[bookingId] || {}),
        [field]: value,
      },
    }));
  };

  const sendTip = (booking) => {
    const draft = tipDrafts[booking.id] || {};
    const tipAmount = Number(draft.amount);
    const paymentMethod = draft.method || 'wallet';

    if (!Number.isFinite(tipAmount) || tipAmount <= 0) {
      Alert.alert('Tip amount required', 'Enter a valid tip amount before sending it.');
      return;
    }

    executeAction(booking.id, async () => {
      const response = await tipCustomerBooking(session?.token, booking.id, {
        tip_amount: tipAmount,
        payment_method: paymentMethod,
      });

      setTipDrafts((current) => ({
        ...current,
        [booking.id]: { amount: '', method: 'wallet' },
      }));

      return response;
    });
  };

  const updatePaymentDraft = (bookingId, method) => {
    setPaymentDrafts((current) => ({
      ...current,
      [bookingId]: { method },
    }));
  };

  const payDue = (booking) => {
    const paymentMethod = paymentDrafts[booking.id]?.method || 'wallet';

    executeAction(booking.id, () =>
      payCustomerBooking(session?.token, booking.id, {
        payment_method: paymentMethod,
      })
    );
  };

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: screenTheme.background }]}>
      <ScrollView contentContainerStyle={styles.container}>
        <View style={[styles.headerCard, { backgroundColor: screenTheme.card, borderColor: screenTheme.border }]}>
          <View style={styles.headerRow}>
            <View style={styles.headerIconWrap}>
              <Feather name="briefcase" size={16} color={screenTheme.text} />
            </View>
            <Text style={[styles.headerTag, { color: screenTheme.accent }]}>{role}</Text>
          </View>
          <Text style={[styles.title, { color: screenTheme.text }]}>Bookings</Text>
          <Text style={[styles.subtitle, { color: screenTheme.muted }]}>Manage active jobs and completion flow.</Text>
        </View>

        {state.loading ? (
          <View style={styles.loadingRow}>
            <ActivityIndicator color={screenTheme.accent} />
            <Text style={[styles.loadingText, { color: screenTheme.muted }]}>Loading bookings...</Text>
          </View>
        ) : null}

        {state.error ? <Text style={styles.errorText}>{state.error}</Text> : null}

        {!state.loading && !state.error && state.bookings.length === 0 ? (
          <View style={styles.emptyCard}>
            <Text style={styles.emptyTitle}>No bookings found</Text>
            <Text style={styles.emptyText}>Bookings will appear here automatically.</Text>
          </View>
        ) : null}

        {state.bookings.map((booking) => {
          const busy = busyBookingId === booking.id;
          const tipDraft = tipDrafts[booking.id] || { amount: '', method: 'wallet' };
          const paymentDraft = paymentDrafts[booking.id] || { method: 'wallet' };
          return (
            <BookingCard
              key={booking.id}
              booking={booking}
              role={role}
              busy={busy}
              tipDraft={tipDraft}
              paymentDraft={paymentDraft}
              onTipDraftChange={updateTipDraft}
              onPaymentDraftChange={updatePaymentDraft}
              onPayDue={payDue}
              onSendTip={sendTip}
              onStartJob={() => executeAction(booking.id, () => startProviderBooking(session?.token, booking.id))}
              onRequestCompletion={() => executeAction(booking.id, () => requestProviderCompletion(session?.token, booking.id))}
              onConfirmCompletion={() => executeAction(booking.id, () => confirmCustomerCompletion(session?.token, booking.id))}
              onReportIssue={() => reportIssue(booking.id)}
              onOpenChat={() =>
                navigation.navigate('BookingChat', {
                  bookingId: booking.id,
                  bookingLabel: `${booking.service_name || 'Service'} (#${booking.id})`,
                })
              }
              theme={screenTheme}
            />
          );
        })}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1, backgroundColor: '#090b10' },
  container: { padding: 18, paddingBottom: 40, gap: 12 },
  headerCard: {
    backgroundColor: '#0f172a',
    borderRadius: 20,
    borderWidth: 1,
    borderColor: '#1f2937',
    padding: 16,
  },
  headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  headerIconWrap: {
    width: 34,
    height: 34,
    borderRadius: 10,
    backgroundColor: '#86efac',
    alignItems: 'center',
    justifyContent: 'center',
  },
  headerTag: {
    color: '#86efac',
    textTransform: 'uppercase',
    fontWeight: '800',
    fontSize: 11,
    letterSpacing: 0.8,
  },
  title: { color: '#f8fafc', fontSize: 28, fontWeight: '900', marginTop: 10 },
  subtitle: { color: '#cbd5e1', marginTop: 4, fontSize: 14 },
  loadingRow: { flexDirection: 'row', alignItems: 'center', gap: 8, paddingVertical: 8 },
  loadingText: { color: '#dcfce7' },
  errorText: { color: '#fca5a5', fontWeight: '700' },
  emptyCard: {
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#1f2937',
    backgroundColor: '#0b1220',
    padding: 14,
  },
  emptyTitle: { color: '#f8fafc', fontWeight: '800', fontSize: 15 },
  emptyText: { color: '#94a3b8', marginTop: 4 },
  jobCard: {
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#1f2937',
    backgroundColor: '#111827',
    padding: 14,
  },
  jobTopRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  jobId: { color: '#93c5fd', fontWeight: '800' },
  jobStatus: { color: '#fcd34d', fontWeight: '800', textTransform: 'capitalize' },
  jobService: { color: '#f8fafc', fontWeight: '800', fontSize: 16, marginTop: 6 },
  jobMeta: { color: '#cbd5e1', marginTop: 3, fontSize: 13 },
  tipPanel: {
    marginTop: 10,
    padding: 12,
    borderRadius: 16,
    backgroundColor: '#0b1220',
    borderWidth: 1,
    borderColor: '#1f2937',
  },
  tipLabel: { color: '#e2e8f0', fontSize: 12, fontWeight: '800', marginBottom: 8, textTransform: 'uppercase' },
  payHint: { color: '#93c5fd', marginBottom: 10, fontSize: 12, fontWeight: '700' },
  tipInput: {
    backgroundColor: '#111827',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#334155',
    color: '#f8fafc',
    paddingHorizontal: 12,
    paddingVertical: 10,
    marginBottom: 10,
  },
  methodRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginBottom: 10 },
  methodChip: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#334155',
    paddingHorizontal: 10,
    paddingVertical: 6,
    backgroundColor: '#111827',
  },
  methodChipActive: {
    backgroundColor: '#86efac',
    borderColor: '#86efac',
  },
  methodChipText: { color: '#cbd5e1', fontSize: 11, fontWeight: '700', textTransform: 'uppercase' },
  methodChipTextActive: { color: '#052e16' },
  tipButton: {
    borderRadius: 12,
    backgroundColor: '#22c55e',
    paddingVertical: 10,
    alignItems: 'center',
  },
  tipButtonDisabled: { opacity: 0.7 },
  tipButtonText: { color: '#052e16', fontWeight: '900' },
  actionsWrap: { marginTop: 10, flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  actionButton: {
    backgroundColor: '#16a34a',
    borderRadius: 10,
    paddingVertical: 9,
    paddingHorizontal: 12,
  },
  actionButtonSecondary: { backgroundColor: '#2563eb' },
  actionButtonDanger: { backgroundColor: '#dc2626' },
  actionButtonDisabled: { opacity: 0.7 },
  actionButtonText: { color: '#ffffff', fontWeight: '800', fontSize: 12 },
});
