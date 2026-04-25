import React, { useState } from 'react';
import { ActivityIndicator, Image, KeyboardAvoidingView, Platform, SafeAreaView, ScrollView, StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';
import { Feather } from '@expo/vector-icons';
import { signUpWithLaravel } from '../services/api';

export default function SignupScreen({ navigation }) {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [role, setRole] = useState('customer');
  const [referralCode, setReferralCode] = useState('');
  const [passwordVisible, setPasswordVisible] = useState(false);
  const [confirmationVisible, setConfirmationVisible] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const handleSignup = async () => {
    setLoading(true);
    setError('');
    setSuccess('');

    try {
      const result = await signUpWithLaravel({
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
        role,
        referral_code: referralCode || undefined,
      });

      setSuccess(result.message || 'Registration complete. Please verify your email before logging in.');
    } catch (exception) {
      setError(exception.message || 'Unable to complete registration.');
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
              <Feather name="user-plus" size={12} color="#e3ff5e" />
              <Text style={styles.authPillText}>Create account</Text>
            </View>
          </View>

          <View style={styles.headerSlots}>
            <View style={styles.logoSlot}>
              <Image source={require('./assets/logo.png')} style={styles.logoImage} resizeMode="contain" />
            </View>
          </View>

          <TouchableOpacity onPress={() => navigation.navigate('Login')} style={styles.backButton}>
            <Feather name="arrow-left" size={16} color="#f4f4f5" />
          </TouchableOpacity>

          <View style={styles.hero}>
            <Text style={styles.kicker}>Create account</Text>
            <Text style={styles.title}>
              HaalChaal <Text style={styles.titleAccent}>Support</Text>
            </Text>
            <Text style={styles.titleSub}>Join the support app</Text>
          </View>

          <View style={styles.formCard}>
            <Text style={styles.formTitle}>Start here</Text>
           

            <Text style={styles.label}>Full name</Text>
            <View style={styles.fieldWrap}>
              <Feather name="user" size={16} color="#e3ff5e" style={styles.fieldIcon} />
              <TextInput
                value={name}
                onChangeText={setName}
                placeholder="Enter your full name"
                placeholderTextColor="#71717a"
                autoComplete="off"
                importantForAutofill="no"
                textContentType="none"
                style={styles.input}
              />
            </View>

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

            <Text style={styles.label}>Confirm password</Text>
            <View style={styles.fieldWrap}>
              <Feather name="lock" size={16} color="#e3ff5e" style={styles.fieldIcon} />
              <TextInput
                value={passwordConfirmation}
                onChangeText={setPasswordConfirmation}
                secureTextEntry={!confirmationVisible}
                placeholder="Confirm your password"
                placeholderTextColor="#71717a"
                autoComplete="off"
                importantForAutofill="no"
                textContentType="none"
                style={[styles.input, styles.passwordInput]}
              />
              <TouchableOpacity onPress={() => setConfirmationVisible((current) => !current)} style={styles.eyeButton}>
                <Feather name={confirmationVisible ? 'eye-off' : 'eye'} size={15} color="#e3ff5e" />
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

            <Text style={styles.label}>Referral code (optional)</Text>
            <View style={styles.fieldWrap}>
              <Feather name="key" size={16} color="#e3ff5e" style={styles.fieldIcon} />
              <TextInput
                value={referralCode}
                onChangeText={setReferralCode}
                autoCapitalize="characters"
                placeholder="Enter referral code (optional)"
                placeholderTextColor="#71717a"
                autoComplete="off"
                importantForAutofill="no"
                textContentType="none"
                style={styles.input}
              />
            </View>

            {error ? <Text style={styles.error}>{error}</Text> : null}
            {success ? <Text style={styles.success}>{success}</Text> : null}

            <TouchableOpacity onPress={handleSignup} style={styles.button} disabled={loading}>
              {loading ? (
                <ActivityIndicator color="#171717" />
              ) : (
                <>
                  <Text style={styles.buttonText}>Create account</Text>
                  <Feather name="arrow-right" size={16} color="#171717" />
                </>
              )}
            </TouchableOpacity>

            <TouchableOpacity onPress={() => navigation.navigate('Login')} style={styles.linkButton}>
              <Text style={styles.linkText}>
                Already have an account? <Text style={styles.linkTextStrong}>Log in</Text>
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
    paddingTop: 16,
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
  headerSlots: {
    alignItems: 'center',
    marginBottom: 10,
  },
  backgroundGlowTop: {
    position: 'absolute',
    top: -120,
    right: -100,
    width: 300,
    height: 300,
    borderRadius: 150,
    backgroundColor: 'rgba(227, 255, 94, 0.1)',
  },
  backgroundGlowBottom: {
    position: 'absolute',
    bottom: -140,
    left: -110,
    width: 320,
    height: 320,
    borderRadius: 160,
    backgroundColor: 'rgba(227, 255, 94, 0.06)',
  },
  backButton: {
    width: 42,
    height: 42,
    borderRadius: 21,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.12)',
    backgroundColor: 'rgba(255,255,255,0.04)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 18,
  },
  backButtonText: {
    color: '#f4f4f5',
    fontSize: 28,
    lineHeight: 28,
    marginTop: -2,
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
  error: {
    marginTop: 12,
    color: '#fca5a5',
    fontSize: 13,
  },
  success: {
    marginTop: 12,
    color: '#bef264',
    fontSize: 13,
  },
  button: {
    marginTop: 18,
    backgroundColor: '#e3ff5e',
    borderRadius: 18,
    minHeight: 52,
    alignItems: 'center',
    justifyContent: 'center',
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