import React, { useMemo, useState } from 'react';
import {
  FlatList,
  Modal,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Switch,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { Feather } from '@expo/vector-icons';
import { DAISYUI_THEME_OPTIONS, getThemeByName } from '../theme/daisyThemes';

export default function SettingsScreen({ themeName, onThemeChange, theme }) {
  const [themePickerVisible, setThemePickerVisible] = useState(false);
  const [notificationsEnabled, setNotificationsEnabled] = useState(true);
  const [soundEnabled, setSoundEnabled] = useState(true);
  const [bookingAlertsEnabled, setBookingAlertsEnabled] = useState(true);

  const activeTheme = useMemo(() => getThemeByName(themeName), [themeName]);

  const selectTheme = (nextTheme) => {
    onThemeChange?.(nextTheme.name);
    setThemePickerVisible(false);
  };

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: theme.background }]}>
      <ScrollView contentContainerStyle={styles.container} showsVerticalScrollIndicator={false}>
        <View style={[styles.sectionCard, { backgroundColor: theme.card, borderColor: theme.border }]}>
          <View style={styles.sectionHeader}>
            <Text style={[styles.sectionTitle, { color: theme.text }]}>Appearance</Text>
            <View style={[styles.pill, { backgroundColor: theme.accentSoft }]}>
              <Text style={[styles.pillText, { color: theme.accent }]}>{activeTheme.label}</Text>
            </View>
          </View>
          <Text style={[styles.sectionCopy, { color: theme.muted }]}>Pick a daisyUI-inspired theme for the support app shell.</Text>
          <TouchableOpacity
            style={[styles.dropdownButton, { borderColor: theme.border, backgroundColor: theme.background }]}
            onPress={() => setThemePickerVisible(true)}
            activeOpacity={0.85}
          >
            <View>
              <Text style={[styles.dropdownLabel, { color: theme.text }]}>Theme</Text>
              <Text style={[styles.dropdownValue, { color: theme.muted }]}>{activeTheme.label}</Text>
            </View>
            <Feather name="chevron-down" size={18} color={theme.accent} />
          </TouchableOpacity>
        </View>

        <View style={[styles.sectionCard, { backgroundColor: theme.card, borderColor: theme.border }]}>
          <Text style={[styles.sectionTitle, { color: theme.text }]}>Notifications</Text>
          <View style={styles.settingRow}>
            <View style={styles.settingTextWrap}>
              <Text style={[styles.settingLabel, { color: theme.text }]}>App notifications</Text>
              <Text style={[styles.settingNote, { color: theme.muted }]}>Enable alert badges and sound.</Text>
            </View>
            <Switch
              value={notificationsEnabled}
              onValueChange={setNotificationsEnabled}
              trackColor={{ false: '#334155', true: theme.accentSoft }}
              thumbColor={notificationsEnabled ? theme.accent : '#cbd5e1'}
            />
          </View>
          <View style={styles.settingRow}>
            <View style={styles.settingTextWrap}>
              <Text style={[styles.settingLabel, { color: theme.text }]}>Sound</Text>
              <Text style={[styles.settingNote, { color: theme.muted }]}>Play a sound for booking and chat updates.</Text>
            </View>
            <Switch
              value={soundEnabled}
              onValueChange={setSoundEnabled}
              trackColor={{ false: '#334155', true: theme.accentSoft }}
              thumbColor={soundEnabled ? theme.accent : '#cbd5e1'}
            />
          </View>
          <View style={styles.settingRow}>
            <View style={styles.settingTextWrap}>
              <Text style={[styles.settingLabel, { color: theme.text }]}>Booking alerts</Text>
              <Text style={[styles.settingNote, { color: theme.muted }]}>Highlight booking chat and payment updates.</Text>
            </View>
            <Switch
              value={bookingAlertsEnabled}
              onValueChange={setBookingAlertsEnabled}
              trackColor={{ false: '#334155', true: theme.accentSoft }}
              thumbColor={bookingAlertsEnabled ? theme.accent : '#cbd5e1'}
            />
          </View>
        </View>
      </ScrollView>

      <Modal visible={themePickerVisible} transparent animationType="fade" onRequestClose={() => setThemePickerVisible(false)}>
        <Pressable style={styles.modalBackdrop} onPress={() => setThemePickerVisible(false)}>
          <Pressable
            style={[styles.sheet, { backgroundColor: theme.background, borderColor: theme.border }]}
            onPress={() => {}}
          >
            <View style={styles.sheetHeader}>
              <Text style={[styles.sheetTitle, { color: theme.text }]}>Choose theme</Text>
              <TouchableOpacity onPress={() => setThemePickerVisible(false)}>
                <Feather name="x" size={18} color={theme.muted} />
              </TouchableOpacity>
            </View>
            <FlatList
              data={DAISYUI_THEME_OPTIONS}
              keyExtractor={(item) => item.name}
              numColumns={2}
              columnWrapperStyle={styles.themeGridRow}
              renderItem={({ item }) => {
                const selected = item.name === themeName;
                return (
                  <TouchableOpacity
                    style={[
                      styles.themeChip,
                      {
                        backgroundColor: item.background,
                        borderColor: selected ? item.accent : item.border,
                      },
                    ]}
                    onPress={() => selectTheme(item)}
                    activeOpacity={0.9}
                  >
                    <View style={[styles.themeSwatch, { backgroundColor: item.accent }]} />
                    <Text style={[styles.themeLabel, { color: item.text }]}>{item.label}</Text>
                    <Text style={[styles.themeName, { color: item.muted }]}>{item.name}</Text>
                  </TouchableOpacity>
                );
              }}
              contentContainerStyle={styles.themeList}
              showsVerticalScrollIndicator={false}
            />
          </Pressable>
        </Pressable>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1 },
  container: { padding: 20, gap: 14 },
  sectionCard: {
    borderWidth: 1,
    borderRadius: 22,
    padding: 16,
    gap: 12,
  },
  sectionHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  sectionTitle: { fontSize: 18, fontWeight: '800' },
  sectionCopy: { fontSize: 13, lineHeight: 19 },
  pill: { paddingHorizontal: 10, paddingVertical: 6, borderRadius: 999 },
  pillText: { fontSize: 11, fontWeight: '800', letterSpacing: 0.6 },
  dropdownButton: {
    borderWidth: 1,
    borderRadius: 16,
    paddingHorizontal: 14,
    paddingVertical: 14,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  dropdownLabel: { fontSize: 12, fontWeight: '700', textTransform: 'uppercase', letterSpacing: 0.8 },
  dropdownValue: { fontSize: 15, fontWeight: '800', marginTop: 2 },
  settingRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 12 },
  settingTextWrap: { flex: 1 },
  settingLabel: { fontSize: 15, fontWeight: '800', marginBottom: 2 },
  settingNote: { fontSize: 12.5, lineHeight: 18 },
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
    maxHeight: '78%',
  },
  sheetHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 },
  sheetTitle: { fontSize: 18, fontWeight: '900' },
  themeList: { paddingBottom: 14 },
  themeGridRow: { gap: 10 },
  themeChip: {
    flex: 1,
    borderWidth: 1,
    borderRadius: 18,
    padding: 12,
    marginBottom: 10,
    minHeight: 94,
  },
  themeSwatch: {
    width: 18,
    height: 18,
    borderRadius: 6,
    marginBottom: 10,
  },
  themeLabel: { fontSize: 15, fontWeight: '900' },
  themeName: { fontSize: 11, marginTop: 4, textTransform: 'uppercase', letterSpacing: 0.7 },
});