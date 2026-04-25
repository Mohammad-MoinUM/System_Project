import React, { useEffect, useMemo, useState, useRef } from 'react';
import {
  ActivityIndicator,
  Animated,
  RefreshControl,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
  Dimensions,
} from 'react-native';
import { Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { BlurView } from 'expo-blur';
import {
  fetchLiveTrackingStatus,
  fetchPaymentStatus,
  fetchSupportOverview,
} from '../services/api';

const { width } = Dimensions.get('window');

/* ─── Animated Feature Card ─────────────────────────────────────────────── */
function FeatureCard({ feature, onPress, index, theme }) {
  const scale = useRef(new Animated.Value(1)).current;
  const opacity = useRef(new Animated.Value(0)).current;
  const translateY = useRef(new Animated.Value(24)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.timing(opacity, {
        toValue: 1,
        duration: 420,
        delay: 200 + index * 90,
        useNativeDriver: true,
      }),
      Animated.spring(translateY, {
        toValue: 0,
        delay: 200 + index * 90,
        tension: 80,
        friction: 10,
        useNativeDriver: true,
      }),
    ]).start();
  }, []);

  const handlePressIn = () =>
    Animated.spring(scale, { toValue: 0.97, useNativeDriver: true }).start();
  const handlePressOut = () =>
    Animated.spring(scale, { toValue: 1, useNativeDriver: true }).start();

  return (
    <Animated.View style={{ opacity, transform: [{ translateY }, { scale }] }}>
      <TouchableOpacity
        onPress={onPress}
        onPressIn={handlePressIn}
        onPressOut={handlePressOut}
        activeOpacity={1}
      >
        <LinearGradient
          colors={[theme.card, theme.background]}
          style={styles.featureCard}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
        >
          {/* Left accent strip */}
          <View style={[styles.featureStrip, { backgroundColor: feature.accent }]} />

          <View style={styles.featureInner}>
            {/* Icon pill */}
            <LinearGradient
              colors={[feature.accent + '28', feature.accentSoft || feature.accent + '12']}
              style={styles.featureIconPill}
            >
              <Feather name={feature.icon} size={20} color={feature.accent} />
            </LinearGradient>

            <View style={styles.featureTextBlock}>
              <View style={styles.featureTitleRow}>
                <Text style={styles.featureTitle}>{feature.title}</Text>
                <View style={[styles.featureBadgePill, { backgroundColor: feature.accent + '20' }]}>
                  <Text style={[styles.featureBadgeText, { color: feature.accent }]}>
                    {feature.badge}
                  </Text>
                </View>
              </View>
              <Text style={styles.featureDescription}>{feature.description}</Text>
            </View>

            <View style={styles.featureChevronWrap}>
              <Feather name="arrow-right" size={16} color={feature.accent} />
            </View>
          </View>
        </LinearGradient>
      </TouchableOpacity>
    </Animated.View>
  );
}

/* ─── Stat Pill ──────────────────────────────────────────────────────────── */
function StatPill({ item, accent, accentSoft, index }) {
  const opacity = useRef(new Animated.Value(0)).current;
  const scale = useRef(new Animated.Value(0.8)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.timing(opacity, {
        toValue: 1,
        duration: 350,
        delay: 100 + index * 70,
        useNativeDriver: true,
      }),
      Animated.spring(scale, {
        toValue: 1,
        delay: 100 + index * 70,
        tension: 90,
        friction: 8,
        useNativeDriver: true,
      }),
    ]).start();
  }, []);

  return (
    <Animated.View style={[styles.statPill, { opacity, transform: [{ scale }], borderColor: accentSoft }]}>
      <Text style={[styles.statValue, { color: accent }]}>{String(item.value)}</Text>
      <Text style={styles.statLabel}>{item.label}</Text>
    </Animated.View>
  );
}

/* ─── Main Screen ────────────────────────────────────────────────────────── */
export default function HomeScreen({ navigation, session, theme }) {
  const screenTheme = theme || {
    background: '#05080f',
    card: '#101826',
    text: '#f8fafc',
    muted: '#cbd5e1',
    border: '#1f2937',
    accent: '#fbbf24',
    accentSoft: 'rgba(251, 191, 36, 0.18)',
    isDark: true,
  };

  const [overview, setOverview] = useState(null);
  const [payments, setPayments] = useState(null);
  const [tracking, setTracking] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');

  const headerOpacity = useRef(new Animated.Value(0)).current;
  const headerY = useRef(new Animated.Value(-20)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.timing(headerOpacity, { toValue: 1, duration: 500, useNativeDriver: true }),
      Animated.spring(headerY, { toValue: 0, tension: 60, friction: 10, useNativeDriver: true }),
    ]).start();
  }, []);

  const role = session?.user?.role || 'customer';

  const dashboardCopy = useMemo(() => {
    if (role === 'provider') {
      return {
        title: 'Provider\nDashboard',
        subtitle: 'Track active jobs, monitor payouts, and stay connected with support on the move.',
        accent: '#38bdf8',
        accentSoft: 'rgba(56, 189, 248, 0.18)',
        gradientColors: ['#0f2744', '#061626'],
        badge: 'PROVIDER MODE',
      };
    }
    return {
      title: 'Customer\nDashboard',
      subtitle: 'Manage payments, follow live bookings, and chat with support — all in one place.',
      accent: '#fbbf24',
      accentSoft: 'rgba(251, 191, 36, 0.18)',
      gradientColors: ['#1a1200', '#0a0800'],
      badge: 'CUSTOMER MODE',
    };
  }, [role]);

  const loadDashboard = async (showPullRefresh = false) => {
    if (showPullRefresh) setRefreshing(true);
    else setLoading(true);
    setError('');
    try {
      const [overviewPayload, paymentsPayload, trackingPayload] = await Promise.all([
        fetchSupportOverview(session?.token),
        fetchPaymentStatus(session?.token),
        fetchLiveTrackingStatus(session?.token),
      ]);
      setOverview(overviewPayload);
      setPayments(paymentsPayload);
      setTracking(trackingPayload);
    } catch (ex) {
      setError(ex.message || 'Unable to load dashboard.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadDashboard();
  }, [session?.token]);

  const features = [
    {
      title: 'Bookings',
      description:
        role === 'provider'
          ? 'Start jobs and request completion from one place.'
          : 'Track active bookings and confirm service completion quickly.',
      badge: 'Core',
      accent: '#a78bfa',
      icon: 'briefcase',
      route: 'Jobs',
    },
    {
      title: 'Payments',
      description:
        role === 'provider'
          ? 'Review earnings and pending payouts before closing your day.'
          : 'Track paid and pending transactions for your bookings.',
      badge: 'Finance',
      accent: '#38bdf8',
      icon: 'credit-card',
      route: 'Payments',
    },
    {
      title: 'Live Location',
      description:
        role === 'provider'
          ? 'Share or verify your location while handling active jobs.'
          : 'Follow your assigned provider in real time.',
      badge: 'Tracking',
      accent: '#34d399',
      icon: 'navigation',
      route: 'Tracking',
    },
    {
      title: 'Chat Support',
      description: 'Send quick support updates and resolve issues without leaving the app.',
      badge:
        Number(overview?.unread_booking_chats || 0) > 0
          ? `Chat ${overview.unread_booking_chats}`
          : 'Support',
      accent: '#f87171',
      icon: 'message-circle',
      route: 'Chat',
    },
  ];

  const statItems =
    role === 'provider'
      ? [
          { label: 'Active Jobs', value: tracking?.active_jobs ?? 0 },
          { label: 'Pending Pay', value: payments?.pending_payment ?? 0 },
          { label: 'Completed', value: payments?.completed_bookings ?? 0 },
        ]
      : [
          { label: 'Bookings', value: payments?.total_bookings ?? 0 },
          { label: 'Pending Pay', value: payments?.pending_payment ?? 0 },
          { label: 'Live', value: tracking?.active_booking_id ? 'Yes' : 'No' },
        ];

  return (
    <SafeAreaView style={[styles.safe, { backgroundColor: screenTheme.background }]}>
      {/* Background blobs */}
      <View style={styles.blobTopRight} />
      <View style={styles.blobBottomLeft} />
      <View style={styles.blobCenter} />

      <ScrollView
        contentContainerStyle={styles.scroll}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={() => loadDashboard(true)}
            tintColor={dashboardCopy.accent}
          />
        }
      >
        {/* ── HERO ── */}
        <Animated.View style={{ opacity: headerOpacity, transform: [{ translateY: headerY }] }}>
          <LinearGradient
            colors={[screenTheme.card, screenTheme.background]}
            style={styles.heroCard}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
          >
            {/* Top row */}
            <View style={styles.heroTopRow}>
              <View style={[styles.modeBadge, { backgroundColor: dashboardCopy.accentSoft }]}>
                <View style={[styles.modeDot, { backgroundColor: dashboardCopy.accent }]} />
                <Text style={[styles.modeBadgeText, { color: dashboardCopy.accent }]}>
                  {dashboardCopy.badge}
                </Text>
              </View>
            </View>

            {/* Title */}
            <Text style={styles.heroTitle}>{dashboardCopy.title}</Text>
            <Text style={styles.heroSubtitle}>{dashboardCopy.subtitle}</Text>

            {/* User chip */}
            <View style={styles.userChip}>
              <LinearGradient
                colors={[dashboardCopy.accent + '30', dashboardCopy.accent + '10']}
                style={styles.avatarCircle}
              >
                <Text style={[styles.avatarLetter, { color: dashboardCopy.accent }]}>
                  {(session?.user?.name ?? 'U')[0].toUpperCase()}
                </Text>
              </LinearGradient>
              <View>
                <Text style={styles.userName}>{session?.user?.name ?? 'Unknown user'}</Text>
                <Text style={styles.userEmail}>{session?.user?.email ?? 'No email'}</Text>
              </View>
            </View>

            {/* Divider */}
            <View style={[styles.divider, { backgroundColor: dashboardCopy.accentSoft }]} />

            {/* Stats */}
            <View style={styles.statRow}>
              {statItems.map((item, i) => (
                <StatPill
                  key={item.label}
                  item={item}
                  accent={dashboardCopy.accent}
                  accentSoft={dashboardCopy.accentSoft}
                  index={i}
                />
              ))}
            </View>
          </LinearGradient>
        </Animated.View>

        {/* ── SYSTEM STATUS ── */}
        <View style={styles.statusCard}>
          <View style={styles.statusHeader}>
            <View style={styles.statusDotWrap}>
              <View style={[styles.statusDot, loading ? styles.dotLoading : styles.dotLive]} />
              {!loading && !error && <View style={[styles.statusDotRing, styles.dotLive]} />}
            </View>
            <Text style={styles.statusTitle}>System Status</Text>
          </View>

          {loading && (
            <View style={styles.loadingRow}>
              <ActivityIndicator size="small" color={screenTheme.accent} />
              <Text style={[styles.statusText, { color: screenTheme.muted }]}>Connecting to dashboard…</Text>
            </View>
          )}
          {error ? <Text style={styles.errorText}>⚠ {error}</Text> : null}
          {overview && !loading && (
            <>
              <Text style={[styles.statusText, { color: screenTheme.muted }]}>{overview.message}</Text>
              <View style={styles.moduleRow}>
                {(overview.modules || []).map((m) => (
                  <View key={m} style={styles.moduleChip}>
                    <Text style={styles.moduleChipText}>{m}</Text>
                  </View>
                ))}
              </View>
            </>
          )}
        </View>

        {/* ── QUICK ACTIONS ── */}
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Quick Actions</Text>
          <View style={styles.sectionLine} />
        </View>

        {features.map((f, i) => (
          <FeatureCard
            key={f.title}
            feature={f}
            onPress={() => navigation.navigate(f.route)}
            index={i}
            theme={screenTheme}
          />
        ))}

        {/* ── FOOTER NOTE ── */}
        <LinearGradient
          colors={['rgba(255,255,255,0.05)', 'rgba(255,255,255,0.02)']}
          style={styles.footerCard}
        >
          <Feather name="zap" size={14} color="#64748b" />
          <Text style={styles.footerText}>
            Mobile-first essentials — payments, live tracking, and fast support chat.
          </Text>
        </LinearGradient>
      </ScrollView>
    </SafeAreaView>
  );
}

/* ─── Styles ─────────────────────────────────────────────────────────────── */
const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: '#05080f',
  },

  /* Background atmosphere */
  blobTopRight: {
    position: 'absolute',
    top: -80,
    right: -80,
    width: 260,
    height: 260,
    borderRadius: 130,
    backgroundColor: 'rgba(56,189,248,0.08)',
  },
  blobBottomLeft: {
    position: 'absolute',
    bottom: -100,
    left: -100,
    width: 300,
    height: 300,
    borderRadius: 150,
    backgroundColor: 'rgba(251,191,36,0.07)',
  },
  blobCenter: {
    position: 'absolute',
    top: '40%',
    left: '25%',
    width: 180,
    height: 180,
    borderRadius: 90,
    backgroundColor: 'rgba(99,102,241,0.06)',
  },

  scroll: {
    padding: 18,
    paddingBottom: 50,
    gap: 14,
  },

  /* Hero card */
  heroCard: {
    borderRadius: 28,
    padding: 22,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.08)',
  },
  heroTopRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 18,
  },
  modeBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 999,
  },
  modeDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
  },
  modeBadgeText: {
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1.2,
  },
  heroTitle: {
    color: '#f1f5f9',
    fontSize: 38,
    fontWeight: '900',
    lineHeight: 44,
    letterSpacing: -0.5,
    marginBottom: 10,
  },
  heroSubtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 21,
    marginBottom: 18,
  },
  userChip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    backgroundColor: 'rgba(255,255,255,0.04)',
    borderRadius: 16,
    padding: 12,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.07)',
  },
  avatarCircle: {
    width: 40,
    height: 40,
    borderRadius: 20,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarLetter: {
    fontSize: 18,
    fontWeight: '900',
  },
  userName: {
    color: '#e2e8f0',
    fontSize: 15,
    fontWeight: '700',
  },
  userEmail: {
    color: '#475569',
    fontSize: 12,
    marginTop: 2,
  },
  divider: {
    height: 1,
    marginVertical: 16,
    borderRadius: 1,
  },

  /* Stats */
  statRow: {
    flexDirection: 'row',
    gap: 10,
  },
  statPill: {
    flex: 1,
    borderRadius: 18,
    paddingVertical: 14,
    alignItems: 'center',
    borderWidth: 1,
    backgroundColor: 'rgba(255,255,255,0.03)',
  },
  statValue: {
    fontSize: 22,
    fontWeight: '900',
    letterSpacing: -0.5,
  },
  statLabel: {
    color: '#475569',
    fontSize: 10,
    fontWeight: '700',
    marginTop: 4,
    textTransform: 'uppercase',
    letterSpacing: 0.8,
  },

  /* System status */
  statusCard: {
    backgroundColor: 'rgba(255,255,255,0.04)',
    borderRadius: 22,
    padding: 18,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.07)',
  },
  statusHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 12,
  },
  statusDotWrap: {
    width: 14,
    height: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  statusDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    position: 'absolute',
  },
  statusDotRing: {
    width: 14,
    height: 14,
    borderRadius: 7,
    opacity: 0.3,
  },
  dotLive: { backgroundColor: '#34d399' },
  dotLoading: { backgroundColor: '#94a3b8' },
  statusTitle: {
    color: '#e2e8f0',
    fontSize: 14,
    fontWeight: '700',
    letterSpacing: 0.3,
  },
  loadingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  statusText: {
    color: '#64748b',
    fontSize: 13,
    lineHeight: 20,
  },
  errorText: {
    color: '#f87171',
    fontSize: 13,
    fontWeight: '600',
  },
  moduleRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 6,
    marginTop: 10,
  },
  moduleChip: {
    backgroundColor: 'rgba(255,255,255,0.06)',
    borderRadius: 8,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.08)',
  },
  moduleChipText: {
    color: '#94a3b8',
    fontSize: 11,
    fontWeight: '600',
  },

  /* Section header */
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 2,
  },
  sectionTitle: {
    color: '#f1f5f9',
    fontSize: 16,
    fontWeight: '800',
    letterSpacing: 0.2,
  },
  sectionLine: {
    flex: 1,
    height: 1,
    backgroundColor: 'rgba(255,255,255,0.06)',
    borderRadius: 1,
  },

  /* Feature cards */
  featureCard: {
    borderRadius: 22,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.07)',
    overflow: 'hidden',
    flexDirection: 'row',
  },
  featureStrip: {
    width: 3,
    borderTopLeftRadius: 22,
    borderBottomLeftRadius: 22,
  },
  featureInner: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    gap: 14,
  },
  featureIconPill: {
    width: 46,
    height: 46,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  featureTextBlock: {
    flex: 1,
  },
  featureTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 4,
  },
  featureTitle: {
    color: '#f1f5f9',
    fontSize: 16,
    fontWeight: '800',
  },
  featureBadgePill: {
    borderRadius: 999,
    paddingHorizontal: 8,
    paddingVertical: 2,
  },
  featureBadgeText: {
    fontSize: 9,
    fontWeight: '800',
    letterSpacing: 0.8,
    textTransform: 'uppercase',
  },
  featureDescription: {
    color: '#64748b',
    fontSize: 12,
    lineHeight: 17,
  },
  featureChevronWrap: {
    width: 30,
    height: 30,
    borderRadius: 999,
    backgroundColor: 'rgba(255,255,255,0.05)',
    alignItems: 'center',
    justifyContent: 'center',
  },

  /* Footer */
  footerCard: {
    borderRadius: 18,
    padding: 16,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.05)',
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 10,
    marginTop: 4,
  },
  footerText: {
    flex: 1,
    color: '#334155',
    fontSize: 12,
    lineHeight: 18,
  },
});