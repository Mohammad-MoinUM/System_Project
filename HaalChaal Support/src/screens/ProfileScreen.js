import React, { useMemo, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  Image,
  Modal,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { Feather } from '@expo/vector-icons';
import { updateCurrentUserPassword, updateCurrentUserProfile } from '../services/api';

export default function ProfileScreen({ session, theme, onUserUpdated }) {
  const userName = session?.user?.name || 'Support User';
  const userEmail = session?.user?.email || 'No email';
  const role = (session?.user?.role || 'customer').toUpperCase();
  const initialPhone = session?.user?.phone || session?.user?.mobile || '';
  const initialCity = session?.user?.city || session?.user?.location || '';
  const [editVisible, setEditVisible] = useState(false);
  const [draftName, setDraftName] = useState(userName);
  const [draftPhone, setDraftPhone] = useState(initialPhone);
  const [draftCity, setDraftCity] = useState(initialCity);
  const [displayName, setDisplayName] = useState(userName);
  const [displayPhone, setDisplayPhone] = useState(initialPhone);
  const [displayCity, setDisplayCity] = useState(initialCity);
  const [saving, setSaving] = useState(false);
  const [passwordVisible, setPasswordVisible] = useState(false);
  const [passwordSaving, setPasswordSaving] = useState(false);
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');

  const userPhotoUrl =
    session?.user?.profile_photo_url ||
    session?.user?.photo_url ||
    session?.user?.avatar_url ||
    session?.user?.image_url ||
    session?.user?.image ||
    session?.user?.avatar ||
    '';

  const avatarSource = useMemo(() => {
    if (userPhotoUrl) {
      return { uri: userPhotoUrl };
    }
    return {
      uri: `https://ui-avatars.com/api/?name=${encodeURIComponent(displayName)}&background=${theme.accent.replace('#', '')}&color=ffffff&bold=true&size=192`,
    };
  }, [displayName, theme.accent, userPhotoUrl]);

  const openEdit = () => {
    setDraftName(displayName);
    setDraftPhone(displayPhone);
    setDraftCity(displayCity);
    setEditVisible(true);
  };

  const saveProfile = async () => {
    const normalizedName = draftName.trim();
    if (!normalizedName) {
      Alert.alert('Profile', 'Name is required.');
      return;
    }

    const payload = {
      name: normalizedName,
      phone: draftPhone.trim() || null,
      city: draftCity.trim() || null,
    };

    try {
      setSaving(true);
      const response = await updateCurrentUserProfile(session?.token, payload);
      const updatedUser = response?.user || {};

      setDisplayName(updatedUser.name || payload.name);
      setDisplayPhone(updatedUser.phone ?? payload.phone ?? '');
      setDisplayCity(updatedUser.city ?? payload.city ?? '');
      setEditVisible(false);
      onUserUpdated?.(updatedUser);
      Alert.alert('Profile updated', response?.message || 'Your profile has been updated.');
    } catch (error) {
      Alert.alert('Update failed', error?.message || 'Could not update profile right now.');
    } finally {
      setSaving(false);
    }
  };

  const resetPasswordForm = () => {
    setCurrentPassword('');
    setNewPassword('');
    setConfirmPassword('');
  };

  const closePasswordModal = () => {
    if (!passwordSaving) {
      setPasswordVisible(false);
      resetPasswordForm();
    }
  };

  const submitPasswordChange = async () => {
    if (!currentPassword || !newPassword || !confirmPassword) {
      Alert.alert('Password', 'All password fields are required.');
      return;
    }

    if (newPassword.length < 8) {
      Alert.alert('Password', 'New password must be at least 8 characters.');
      return;
    }

    if (newPassword !== confirmPassword) {
      Alert.alert('Password', 'New password and confirm password do not match.');
      return;
    }

    try {
      setPasswordSaving(true);
      const response = await updateCurrentUserPassword(session?.token, {
        current_password: currentPassword,
        password: newPassword,
        password_confirmation: confirmPassword,
      });

      setPasswordVisible(false);
      resetPasswordForm();
      Alert.alert('Password updated', response?.message || 'Your password has been changed.');
    } catch (error) {
      Alert.alert('Update failed', error?.message || 'Could not update password right now.');
    } finally {
      setPasswordSaving(false);
    }
  };

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: theme.background }]}>
      <ScrollView contentContainerStyle={styles.container} showsVerticalScrollIndicator={false}>
        <View style={[styles.heroCard, { backgroundColor: theme.card, borderColor: theme.border }]}>
          <View style={styles.avatarWrap}>
            <Image source={avatarSource} style={styles.avatarImage} resizeMode="cover" />
          </View>
          <View style={styles.heroTextWrap}>
            <Text style={[styles.eyebrow, { color: theme.muted }]}>Profile</Text>
            <Text style={[styles.title, { color: theme.text }]}>{displayName}</Text>
            <Text style={[styles.subtitle, { color: theme.muted }]}>{userEmail}</Text>
            <Text style={[styles.role, { color: theme.accent }]}>{role}</Text>
          </View>
        </View>

        <View style={[styles.actionsCard, { backgroundColor: theme.card, borderColor: theme.border }]}>
          <TouchableOpacity
            style={[styles.primaryButton, { backgroundColor: theme.accent }]}
            onPress={openEdit}
            activeOpacity={0.9}
          >
            <Feather name="edit-3" size={16} color="#ffffff" />
            <Text style={styles.primaryButtonText}>Edit profile</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.secondaryButton, { borderColor: theme.border, backgroundColor: theme.background }]}
            onPress={() => setPasswordVisible(true)}
            activeOpacity={0.85}
          >
            <Feather name="lock" size={16} color={theme.text} />
            <Text style={[styles.secondaryButtonText, { color: theme.text }]}>Change password</Text>
          </TouchableOpacity>
        </View>

        <View style={[styles.sectionCard, { backgroundColor: theme.card, borderColor: theme.border }]}>
          <Text style={[styles.sectionTitle, { color: theme.text }]}>Personal information</Text>
          <View style={styles.infoRow}>
            <Text style={[styles.infoLabel, { color: theme.muted }]}>Full name</Text>
            <Text style={[styles.infoValue, { color: theme.text }]}>{displayName}</Text>
          </View>
          <View style={styles.infoRow}>
            <Text style={[styles.infoLabel, { color: theme.muted }]}>Email</Text>
            <Text style={[styles.infoValue, { color: theme.text }]}>{userEmail}</Text>
          </View>
          <View style={styles.infoRow}>
            <Text style={[styles.infoLabel, { color: theme.muted }]}>Phone</Text>
            <Text style={[styles.infoValue, { color: theme.text }]}>{displayPhone || 'Not set'}</Text>
          </View>
          <View style={styles.infoRow}>
            <Text style={[styles.infoLabel, { color: theme.muted }]}>City</Text>
            <Text style={[styles.infoValue, { color: theme.text }]}>{displayCity || 'Not set'}</Text>
          </View>
          <View style={styles.infoRow}>
            <Text style={[styles.infoLabel, { color: theme.muted }]}>Role</Text>
            <Text style={[styles.infoValue, { color: theme.text }]}>{role}</Text>
          </View>
        </View>
      </ScrollView>

      <Modal visible={editVisible} transparent animationType="fade" onRequestClose={() => setEditVisible(false)}>
        <Pressable style={styles.modalBackdrop} onPress={() => setEditVisible(false)}>
          <Pressable style={[styles.sheet, { backgroundColor: theme.card, borderColor: theme.border }]} onPress={() => {}}>
            <View style={styles.sheetHeader}>
              <Text style={[styles.sheetTitle, { color: theme.text }]}>Edit profile</Text>
              <TouchableOpacity onPress={() => setEditVisible(false)}>
                <Feather name="x" size={18} color={theme.muted} />
              </TouchableOpacity>
            </View>

            <Text style={[styles.inputLabel, { color: theme.muted }]}>Full name</Text>
            <TextInput
              style={[styles.input, { borderColor: theme.border, color: theme.text, backgroundColor: theme.background }]}
              value={draftName}
              onChangeText={setDraftName}
              placeholder="Your full name"
              placeholderTextColor={theme.muted}
            />

            <Text style={[styles.inputLabel, { color: theme.muted }]}>Phone</Text>
            <TextInput
              style={[styles.input, { borderColor: theme.border, color: theme.text, backgroundColor: theme.background }]}
              value={draftPhone}
              onChangeText={setDraftPhone}
              keyboardType="phone-pad"
              placeholder="Phone number"
              placeholderTextColor={theme.muted}
            />

            <Text style={[styles.inputLabel, { color: theme.muted }]}>City</Text>
            <TextInput
              style={[styles.input, { borderColor: theme.border, color: theme.text, backgroundColor: theme.background }]}
              value={draftCity}
              onChangeText={setDraftCity}
              placeholder="City"
              placeholderTextColor={theme.muted}
            />

            <TouchableOpacity
              style={[styles.saveButton, { backgroundColor: theme.accent, opacity: saving ? 0.8 : 1 }]}
              onPress={saveProfile}
              disabled={saving}
              activeOpacity={0.9}
            >
              {saving ? <ActivityIndicator color="#ffffff" /> : <Text style={styles.saveButtonText}>Save changes</Text>}
            </TouchableOpacity>
          </Pressable>
        </Pressable>
      </Modal>

      <Modal visible={passwordVisible} transparent animationType="fade" onRequestClose={closePasswordModal}>
        <Pressable style={styles.modalBackdrop} onPress={closePasswordModal}>
          <Pressable style={[styles.sheet, { backgroundColor: theme.card, borderColor: theme.border }]} onPress={() => {}}>
            <View style={styles.sheetHeader}>
              <Text style={[styles.sheetTitle, { color: theme.text }]}>Change password</Text>
              <TouchableOpacity onPress={closePasswordModal} disabled={passwordSaving}>
                <Feather name="x" size={18} color={theme.muted} />
              </TouchableOpacity>
            </View>

            <Text style={[styles.inputLabel, { color: theme.muted }]}>Current password</Text>
            <TextInput
              style={[styles.input, { borderColor: theme.border, color: theme.text, backgroundColor: theme.background }]}
              value={currentPassword}
              onChangeText={setCurrentPassword}
              secureTextEntry
              placeholder="Enter current password"
              placeholderTextColor={theme.muted}
            />

            <Text style={[styles.inputLabel, { color: theme.muted }]}>New password</Text>
            <TextInput
              style={[styles.input, { borderColor: theme.border, color: theme.text, backgroundColor: theme.background }]}
              value={newPassword}
              onChangeText={setNewPassword}
              secureTextEntry
              placeholder="At least 8 characters"
              placeholderTextColor={theme.muted}
            />

            <Text style={[styles.inputLabel, { color: theme.muted }]}>Confirm new password</Text>
            <TextInput
              style={[styles.input, { borderColor: theme.border, color: theme.text, backgroundColor: theme.background }]}
              value={confirmPassword}
              onChangeText={setConfirmPassword}
              secureTextEntry
              placeholder="Retype new password"
              placeholderTextColor={theme.muted}
            />

            <TouchableOpacity
              style={[styles.saveButton, { backgroundColor: theme.accent, opacity: passwordSaving ? 0.8 : 1 }]}
              onPress={submitPasswordChange}
              disabled={passwordSaving}
              activeOpacity={0.9}
            >
              {passwordSaving ? <ActivityIndicator color="#ffffff" /> : <Text style={styles.saveButtonText}>Update password</Text>}
            </TouchableOpacity>
          </Pressable>
        </Pressable>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1 },
  container: { padding: 20, gap: 14 },
  heroCard: {
    borderRadius: 24,
    padding: 18,
    borderWidth: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
  },
  avatarWrap: {
    width: 62,
    height: 62,
    borderRadius: 18,
    overflow: 'hidden',
    backgroundColor: '#0f172a',
  },
  avatarImage: {
    width: '100%',
    height: '100%',
  },
  heroTextWrap: { flex: 1 },
  eyebrow: { textTransform: 'uppercase', letterSpacing: 1, fontSize: 11, fontWeight: '800', marginBottom: 4 },
  title: { fontSize: 26, fontWeight: '900', marginBottom: 2 },
  subtitle: { fontSize: 13, lineHeight: 19 },
  role: { marginTop: 8, fontSize: 11, fontWeight: '800', letterSpacing: 1 },
  actionsCard: {
    borderWidth: 1,
    borderRadius: 22,
    padding: 16,
    gap: 10,
  },
  primaryButton: {
    borderRadius: 14,
    minHeight: 44,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    flexDirection: 'row',
  },
  primaryButtonText: { color: '#ffffff', fontWeight: '800', fontSize: 14 },
  secondaryButton: {
    borderRadius: 14,
    minHeight: 44,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    flexDirection: 'row',
    borderWidth: 1,
  },
  secondaryButtonText: { fontWeight: '700', fontSize: 14 },
  sectionCard: {
    borderWidth: 1,
    borderRadius: 22,
    padding: 16,
    gap: 10,
  },
  sectionTitle: { fontSize: 18, fontWeight: '800' },
  infoRow: { flexDirection: 'row', justifyContent: 'space-between', gap: 12, paddingVertical: 6 },
  infoLabel: { fontSize: 13, fontWeight: '700' },
  infoValue: { fontSize: 13, fontWeight: '800', flexShrink: 1, textAlign: 'right' },
  modalBackdrop: {
    flex: 1,
    backgroundColor: 'rgba(2, 6, 23, 0.72)',
    justifyContent: 'flex-end',
  },
  sheet: {
    borderTopLeftRadius: 28,
    borderTopRightRadius: 28,
    borderWidth: 1,
    padding: 18,
  },
  sheetHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 },
  sheetTitle: { fontSize: 18, fontWeight: '900' },
  inputLabel: {
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
    marginBottom: 6,
    marginTop: 8,
    letterSpacing: 0.6,
  },
  input: {
    borderWidth: 1,
    borderRadius: 14,
    minHeight: 46,
    paddingHorizontal: 12,
    fontSize: 14,
  },
  saveButton: {
    borderRadius: 14,
    minHeight: 46,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 16,
  },
  saveButtonText: { color: '#ffffff', fontWeight: '800', fontSize: 15 },
});