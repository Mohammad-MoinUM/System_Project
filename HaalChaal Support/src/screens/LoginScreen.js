import React, { useState } from 'react';
import { ActivityIndicator, Image, KeyboardAvoidingView, Platform, SafeAreaView, ScrollView, StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';
import { Feather } from '@expo/vector-icons';
import { signInWithLaravelCredentials } from '../services/api';

export default function LoginScreen({ onSignIn, navigation }) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [role, setRole] = useState('customer');
  const [passwordVisible, setPasswordVisible] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleLogin = async () => {
    setLoading(true);
    setError('');

    try {
      const session = await signInWithLaravelCredentials({ email, password, role });
      onSignIn(session);
    } catch (exception) {
      setError(exception.message || 'Unable to sign in right now.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.backgroundGlowTop} />
      <View style={styles.backgroundGlowBottom} />
      <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={styles.flex}>
        <ScrollView contentContainerStyle={styles.scrollContent} keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false}>
          <View style={styles.authBar}>
            <View style={styles.authBrandWrap}>
              <View style={styles.authBrandDot} />
              <Text style={styles.authBrandText}>HaalChaal Support</Text>
            </View>
            <View style={styles.authPill}>
              <Feather name="shield" size={12} color="#e3ff5e" />
              <Text style={styles.authPillText}>Secure access</Text>
            </View>
          </View>

          <View style={styles.brandRow}>
            <View style={styles.logoSlot}>
              <Image source={require('./assets/logo.png')} style={styles.logoImage} resizeMode="contain" />
            </View>
          </View>

          <View style={styles.hero}>
            <Text style={styles.kicker}>Support access</Text>
            <Text style={styles.title}>
              HaalChaal <Text style={styles.titleAccent}>Support</Text>
            </Text>
            <Text style={styles.titleSub}>Login to continue</Text>
          </View>

          <View style={styles.formCard}>
            <Text style={styles.formTitle}>Welcome back</Text>

            <Text style={styles.label}>Email</Text>
            <View style={styles.fieldWrap}>
              <Feather name="mail" size={16} color="#e3ff5e" style={styles.fieldIcon} />
              <TextInput
                value={email}
                onChangeText={setEmail}
                autoCapitalize="none"
                keyboardType="email-address"
                autoComplete="off"
                importantForAutofill="no"
                textContentType="none"
                placeholder="Enter your email"
                placeholderTextColor="#71717a"
                style={styles.input}
              />
            </View>

            <Text style={styles.label}>Password</Text>
            <View style={styles.fieldWrap}>
              <Feather name="lock" size={16} color="#e3ff5e" style={styles.fieldIcon} />
              <TextInput
                value={password}
                onChangeText={setPassword}
                secureTextEntry={!passwordVisible}
                placeholder="Enter your password"
                placeholderTextColor="#71717a"
                autoComplete="off"
                importantForAutofill="no"
                textContentType="none"
                style={[styles.input, styles.passwordInput]}
              />
              <TouchableOpacity onPress={() => setPasswordVisible((current) => !current)} style={styles.eyeButton}>
                <Feather name={passwordVisible ? 'eye-off' : 'eye'} size={15} color="#e3ff5e" />
              </TouchableOpacity>
            </View>

            <Text style={styles.label}>Account type</Text>
            <View style={styles.roleRow}>
              {['customer', 'provider'].map((item) => (
                <TouchableOpacity
                  key={item}
                  onPress={() => setRole(item)}
                  style={[styles.roleChip, role === item && styles.roleChipActive]}
                >
                  <Feather name={item === 'customer' ? 'user' : 'briefcase'} size={14} color={role === item ? '#111111' : '#d4d4d8'} style={styles.roleIcon} />
                  <Text style={[styles.roleChipText, role === item && styles.roleChipTextActive]}>{item}</Text>
                </TouchableOpacity>
              ))}
            </View>

            <View style={styles.utilityRow}>
              <TouchableOpacity style={styles.rememberRow}>
                <View style={styles.checkbox}>
                  <Text style={styles.checkboxMark}>✓</Text>
                </View>
                <Text style={styles.utilityText}>Remember me</Text>
              </TouchableOpacity>
              <TouchableOpacity>
                <Text style={styles.forgotText}>Forgot password?</Text>
              </TouchableOpacity>
            </View>

            {error ? <Text style={styles.error}>{error}</Text> : null}

            <TouchableOpacity onPress={handleLogin} style={styles.button} disabled={loading}>
              {loading ? (
                <ActivityIndicator color="#171717" />
              ) : (
                <>
                  <Text style={styles.buttonText}>Log in</Text>
                  <Feather name="arrow-right" size={16} color="#171717" />
                </>
              )}
            </TouchableOpacity>

            <TouchableOpacity onPress={() => navigation.navigate('Signup')} style={styles.linkButton}>
              <Text style={styles.linkText}>
                Don&apos;t have an account? <Text style={styles.linkTextStrong}>Create one</Text>
              </Text>
            </TouchableOpacity>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1, backgroundColor: '#090b10' },
  flex: { flex: 1 },
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: 20,
    paddingTop: 20,
    paddingBottom: 28,
  },
  authBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 16,
    paddingHorizontal: 4,
  },
  authBrandWrap: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  authBrandDot: {
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: '#e3ff5e',
    shadowColor: '#e3ff5e',
    shadowOpacity: 0.45,
    shadowRadius: 10,
  },
  authBrandText: {
    color: '#f8fafc',
    fontSize: 14,
    fontWeight: '800',
    letterSpacing: 0.4,
  },
  authPill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 10,
    paddingVertical: 7,
    borderRadius: 999,
    backgroundColor: 'rgba(227, 255, 94, 0.12)',
    borderWidth: 1,
    borderColor: 'rgba(227, 255, 94, 0.2)',
  },
  authPillText: {
    color: '#e3ff5e',
    fontSize: 11,
    fontWeight: '800',
  },
  backgroundGlowTop: {
    position: 'absolute',
    top: -120,
    right: -90,
    width: 280,
    height: 280,
    borderRadius: 140,
    backgroundColor: 'rgba(227, 255, 94, 0.12)',
  },
  backgroundGlowBottom: {
    position: 'absolute',
    bottom: -130,
    left: -100,
    width: 300,
    height: 300,
    borderRadius: 150,
    backgroundColor: 'rgba(227, 255, 94, 0.08)',
  },
  brandRow: {
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 26,
  },
  logoSlot: {
    width: 260,
    height: 160,
    borderRadius: 34,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'rgba(255,255,255,0.02)',
    shadowColor: '#e3ff5e',
    shadowOpacity: 0.14,
    shadowRadius: 18,
    shadowOffset: { width: 0, height: 8 },
    elevation: 6,
  },
  logoImage: {
    width: '86%',
    height: '86%',
  },
  brandRowGlow: {
    marginTop: 8,
  },
  hero: {
    marginBottom: 18,
  },
  kicker: {
    color: '#e3ff5e',
    textTransform: 'uppercase',
    letterSpacing: 1.8,
    fontSize: 11,
    fontWeight: '800',
    marginBottom: 10,
  },
  title: {
    fontSize: 35,
    fontWeight: '900',
    color: '#f8fafc',
    marginBottom: 4,
    textAlign: 'center',
  },
  titleAccent: {
    color: '#e3ff5e',
  },
  titleSub: {
    color: '#e2e8f0',
    fontSize: 18,
    fontWeight: '800',
    textAlign: 'center',
    marginBottom: 10,
  },
  subtitle: {
    fontSize: 15,
    lineHeight: 22,
    color: '#a1a1aa',
    textAlign: 'center',
  },
  formCard: {
    backgroundColor: 'rgba(24, 28, 35, 0.96)',
    borderRadius: 28,
    padding: 18,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.08)',
    shadowColor: '#000000',
    shadowOpacity: 0.32,
    shadowRadius: 24,
    shadowOffset: { width: 0, height: 16 },
    elevation: 8,
  },
  formTitle: {
    color: '#ffffff',
    fontSize: 20,
    fontWeight: '800',
    marginBottom: 6,
  },
  formSubtitle: {
    color: '#a1a1aa',
    fontSize: 13,
    lineHeight: 19,
    marginBottom: 16,
  },
  label: {
    fontSize: 12,
    fontWeight: '700',
    color: '#d4d4d8',
    marginBottom: 8,
    marginTop: 10,
    textTransform: 'uppercase',
    letterSpacing: 0.8,
  },
  fieldWrap: {
    flexDirection: 'row',
    alignItems: 'center',
    minHeight: 52,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.12)',
    backgroundColor: 'rgba(255,255,255,0.04)',
    paddingHorizontal: 14,
  },
  fieldIcon: {
    marginRight: 12,
    width: 18,
  },
  input: {
    flex: 1,
    color: '#ffffff',
    fontSize: 15,
    paddingVertical: 12,
  },
  passwordInput: {
    paddingRight: 8,
  },
  eyeButton: {
    paddingHorizontal: 2,
    paddingVertical: 8,
  },
  roleRow: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 6,
  },
  roleChip: {
    flex: 1,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.12)',
    backgroundColor: 'rgba(255,255,255,0.04)',
    paddingVertical: 11,
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'center',
  },
  roleChipActive: {
    backgroundColor: '#e3ff5e',
    borderColor: '#e3ff5e',
  },
  roleChipText: {
    color: '#d4d4d8',
    fontWeight: '700',
    textTransform: 'capitalize',
    fontSize: 13,
  },
  roleChipTextActive: {
    color: '#111111',
  },
  roleIcon: {
    marginRight: 6,
  },
  utilityRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: 10,
  },
  rememberRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  checkbox: {
    width: 18,
    height: 18,
    borderRadius: 5,
    borderWidth: 1,
    borderColor: '#e3ff5e',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 8,
    backgroundColor: 'rgba(227, 255, 94, 0.08)',
  },
  checkboxMark: {
    color: '#e3ff5e',
    fontSize: 11,
    fontWeight: '800',
  },
  utilityText: {
    color: '#d4d4d8',
    fontSize: 13,
  },
  forgotText: {
    color: '#e3ff5e',
    fontSize: 13,
    fontWeight: '700',
  },
  error: {
    marginTop: 12,
    color: '#fca5a5',
    fontSize: 13,
  },
  button: {
    marginTop: 18,
    backgroundColor: '#e3ff5e',
    borderRadius: 18,
    minHeight: 52,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
    gap: 8,
    shadowColor: '#e3ff5e',
    shadowOpacity: 0.38,
    shadowRadius: 14,
    shadowOffset: { width: 0, height: 8 },
    elevation: 5,
  },
  buttonText: {
    color: '#121212',
    fontWeight: '900',
    fontSize: 15,
    letterSpacing: 0.3,
  },
  linkButton: {
    marginTop: 16,
    alignItems: 'center',
  },
  linkText: {
    color: '#a1a1aa',
    fontWeight: '600',
    fontSize: 13,
    textAlign: 'center',
  },
  linkTextStrong: {
    color: '#e3ff5e',
    fontWeight: '800',
  },
});