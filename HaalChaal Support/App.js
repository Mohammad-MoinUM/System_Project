import 'react-native-gesture-handler';
import React, { useMemo, useState } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { StatusBar } from 'expo-status-bar';
import AppNavigator from './src/navigation/AppNavigator';
import { DEFAULT_DAISYUI_THEME, getNavigationTheme, getThemeByName } from './src/theme/daisyThemes';

export default function App() {
  const [session, setSession] = useState(null);
  const [themeName, setThemeName] = useState(DEFAULT_DAISYUI_THEME);

  const activeTheme = useMemo(() => getThemeByName(themeName), [themeName]);
  const navigationTheme = useMemo(() => getNavigationTheme(activeTheme), [activeTheme]);

  const authActions = useMemo(() => ({
    signIn: (nextSession) => setSession(nextSession),
    signOut: () => setSession(null),
    updateUser: (nextUser) => {
      setSession((current) => {
        if (!current) return current;
        return {
          ...current,
          user: {
            ...current.user,
            ...nextUser,
          },
        };
      });
    },
  }), []);

  const themeActions = useMemo(() => ({
    setThemeName,
  }), []);

  return (
    <NavigationContainer theme={navigationTheme}>
      <StatusBar style={activeTheme.isDark ? 'light' : 'dark'} />
      <AppNavigator
        session={session}
        authActions={authActions}
        theme={activeTheme}
        themeActions={themeActions}
      />
    </NavigationContainer>
  );
}
