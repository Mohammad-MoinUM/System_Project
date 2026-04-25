import React, { useEffect } from 'react';
import { Image, SafeAreaView, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { Feather } from '@expo/vector-icons';

export default function SplashScreen({ navigation }) {
  useEffect(() => {
    const timer = setTimeout(() => {
      navigation.replace('Login');
    }, 250);

    return () => clearTimeout(timer);
  }, [navigation]);

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.backgroundGlowTop} />
      <View style={styles.backgroundGlowBottom} />

      <View style={styles.container}>
        <View style={styles.iconRow}>
          <View style={styles.iconPill}>
            <Feather name="shield" size={16} color="#e3ff5e" />
          </View>
          <View style={styles.iconPill}>
            <Feather name="activity" size={16} color="#e3ff5e" />
          </View>
          <View style={styles.iconPill}>
            <Feather name="star" size={16} color="#e3ff5e" />
          </View>
        </View>

        <View style={styles.logoSlot}>
          <Image source={require('./assets/logo.png')} style={styles.logoImage} resizeMode="contain" />
        </View>

        <Text style={styles.brandName}>
          HaalChaal <Text style={styles.brandAccent}>Support</Text>
        </Text>
        <Text style={styles.brandTagline}>A focused companion app for support, tracking, and payments.</Text>

        <View style={styles.buttonRow}>
          <TouchableOpacity onPress={() => navigation.replace('Login')} style={styles.primaryButton}>
            <Text style={styles.primaryButtonText}>Get started</Text>
          </TouchableOpacity>
          <TouchableOpacity onPress={() => navigation.replace('Signup')} style={styles.secondaryButton}>
            <Text style={styles.secondaryButtonText}>Create account</Text>
          </TouchableOpacity>
        </View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#090b10',
  },
  backgroundGlowTop: {
    position: 'absolute',
    top: -110,
    right: -90,
    width: 260,
    height: 260,
    borderRadius: 130,
    backgroundColor: 'rgba(227, 255, 94, 0.12)',
  },
  backgroundGlowBottom: {
    position: 'absolute',
    bottom: -120,
    left: -100,
    width: 300,
    height: 300,
    borderRadius: 150,
    backgroundColor: 'rgba(227, 255, 94, 0.08)',
  },
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 24,
  },
  iconRow: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 16,
  },
  iconPill: {
    width: 34,
    height: 34,
    borderRadius: 17,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'rgba(255,255,255,0.05)',
    borderWidth: 1,
    borderColor: 'rgba(227, 255, 94, 0.18)',
  },
  logoSlot: {
    width: 260,
    height: 160,
    borderRadius: 34,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 18,
    backgroundColor: 'rgba(255,255,255,0.03)',
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
  brandName: {
    marginTop: 26,
    color: '#f8fafc',
    fontSize: 31,
    fontWeight: '900',
    letterSpacing: 0.3,
    textAlign: 'center',
  },
  brandAccent: {
    color: '#e3ff5e',
  },
  brandTagline: {
    marginTop: 10,
    color: '#cbd5e1',
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
    maxWidth: 300,
  },
  buttonRow: {
    width: '100%',
    marginTop: 28,
    gap: 12,
  },
  primaryButton: {
    minHeight: 52,
    borderRadius: 18,
    backgroundColor: '#e3ff5e',
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
    gap: 8,
  },
  primaryButtonText: {
    color: '#111111',
    fontSize: 15,
    fontWeight: '900',
  },
  secondaryButton: {
    minHeight: 52,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.12)',
    backgroundColor: 'rgba(255,255,255,0.04)',
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
    gap: 8,
  },
  secondaryButtonText: {
    color: '#f4f4f5',
    fontSize: 15,
    fontWeight: '800',
  },
});