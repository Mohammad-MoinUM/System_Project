import React from 'react';
import { StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { Feather } from '@expo/vector-icons';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import {
  createDrawerNavigator,
  DrawerContentScrollView,
  DrawerItem,
  DrawerItemList,
} from '@react-navigation/drawer';
import HomeScreen from '../screens/HomeScreen';
import JobsScreen from '../screens/JobsScreen';
import PaymentsScreen from '../screens/PaymentsScreen';
import TrackingScreen from '../screens/TrackingScreen';
import ChatScreen from '../screens/ChatScreen';
import BookingChatScreen from '../screens/BookingChatScreen';
import ProfileScreen from '../screens/ProfileScreen';
import SettingsScreen from '../screens/SettingsScreen';
import NotificationsScreen from '../screens/NotificationsScreen';
import SplashScreen from '../screens/SplashScreen';
import LoginScreen from '../screens/LoginScreen';
import SignupScreen from '../screens/SignupScreen';
import { logout } from '../services/api';

const Stack = createNativeStackNavigator();
const Drawer = createDrawerNavigator();

function screenIcon(routeName) {
  if (routeName === 'Home') return 'home';
  if (routeName === 'Jobs') return 'briefcase';
  if (routeName === 'Payments') return 'credit-card';
  if (routeName === 'Tracking') return 'navigation';
  if (routeName === 'Chat') return 'message-circle';
  if (routeName === 'Profile') return 'user';
  if (routeName === 'Settings') return 'settings';
  if (routeName === 'Notifications') return 'bell';
  return 'circle';
}

function SupportDrawerContent({ session, onSignOut, theme, ...props }) {
  const userName = session?.user?.name || 'Support User';
  const userEmail = session?.user?.email || 'No email';
  const role = (session?.user?.role || 'customer').toUpperCase();

  return (
    <View style={[styles.drawerShell, { backgroundColor: theme.card }]}>
      <DrawerContentScrollView
        {...props}
        contentContainerStyle={styles.drawerScrollContent}
        showsVerticalScrollIndicator={false}
      >
        <View style={[styles.profileWrap, { borderBottomColor: theme.border }]}>
          <View style={[styles.avatarCircle, { backgroundColor: theme.accent }]}>
            <Text style={styles.avatarText}>{userName[0]?.toUpperCase() || 'U'}</Text>
          </View>
          <View style={styles.profileTextWrap}>
            <Text style={[styles.helloText, { color: theme.text }]}>Hello,</Text>
            <Text style={[styles.nameText, { color: theme.text }]}>{userName}</Text>
            <Text style={[styles.emailText, { color: theme.muted }]}>{userEmail}</Text>
            <Text style={[styles.roleText, { color: theme.accent }]}>{role}</Text>
          </View>
        </View>

        <DrawerItemList {...props} />
      </DrawerContentScrollView>

      <View style={[styles.logoutWrap, { borderTopColor: theme.border }]}>
        <DrawerItem
          label="Logout"
          labelStyle={[styles.logoutLabel, { color: theme.text }]}
          icon={({ size }) => <Feather name="log-out" size={size} color={theme.text} />}
          onPress={onSignOut}
          style={styles.logoutItem}
        />
      </View>
    </View>
  );
}

function AuthStack({ authActions }) {
  return (
    <Stack.Navigator
      initialRouteName="Splash"
      screenOptions={{
        headerShown: false,
        contentStyle: { backgroundColor: '#090b10' },
      }}
    >
      <Stack.Screen name="Splash" options={{ title: 'Welcome' }}>
        {(props) => <SplashScreen {...props} />}
      </Stack.Screen>
      <Stack.Screen name="Login" options={{ title: 'Login' }}>
        {(props) => <LoginScreen {...props} onSignIn={authActions.signIn} />}
      </Stack.Screen>
      <Stack.Screen name="Signup" options={{ title: 'Sign up' }}>
        {(props) => <SignupScreen {...props} />}
      </Stack.Screen>
    </Stack.Navigator>
  );
}

function AppDrawer({ session, authActions, theme, themeActions }) {
  const handleSignOut = async () => {
    try {
      if (session?.token) await logout(session.token);
    } catch (_) {}
    authActions.signOut();
  };

  return (
    <Drawer.Navigator
      initialRouteName="Home"
      drawerContent={(props) => (
        <SupportDrawerContent {...props} session={session} onSignOut={handleSignOut} theme={theme} />
      )}
      screenOptions={({ navigation, route }) => ({
        headerStyle: { backgroundColor: theme.card },
        headerTintColor: theme.text,
        sceneContainerStyle: { backgroundColor: theme.background },
        drawerType: 'slide',
        drawerStyle: [styles.drawerStyle, { backgroundColor: theme.card, borderRightColor: theme.border }],
        drawerActiveTintColor: theme.text,
        drawerInactiveTintColor: theme.muted,
        drawerActiveBackgroundColor: theme.accentSoft,
        drawerItemStyle: styles.drawerItem,
        drawerLabelStyle: [styles.drawerLabel, { color: theme.text }],
        headerLeft: () => (
          <TouchableOpacity
            style={[styles.menuButton, { backgroundColor: theme.accentSoft }]}
            onPress={() => navigation.toggleDrawer()}
            activeOpacity={0.8}
          >
            <Feather name="menu" size={22} color={theme.text} />
          </TouchableOpacity>
        ),
        drawerIcon: ({ color, size }) => (
          <Feather name={screenIcon(route.name)} size={size} color={color} />
        ),
      })}
    >
      <Drawer.Screen name="Home" options={{ title: 'HaalChaal Support' }}>
        {(props) => <HomeScreen {...props} session={session} theme={theme} />}
      </Drawer.Screen>
      <Drawer.Screen name="Payments" options={{ title: 'Payments' }}>
        {(props) => <PaymentsScreen {...props} session={session} theme={theme} />}
      </Drawer.Screen>
      <Drawer.Screen name="Jobs" options={{ title: 'Bookings' }}>
        {(props) => <JobsScreen {...props} session={session} theme={theme} />}
      </Drawer.Screen>
      <Drawer.Screen name="Tracking" options={{ title: 'Live Tracking' }}>
        {(props) => <TrackingScreen {...props} session={session} theme={theme} />}
      </Drawer.Screen>
      <Drawer.Screen name="Chat" options={{ title: 'Support Chat' }}>
        {(props) => <ChatScreen {...props} session={session} theme={theme} />}
      </Drawer.Screen>
      <Drawer.Screen name="Profile" options={{ title: 'Profile' }}>
        {(props) => <ProfileScreen {...props} session={session} theme={theme} onUserUpdated={authActions.updateUser} />}
      </Drawer.Screen>
      <Drawer.Screen name="Settings" options={{ title: 'Settings' }}>
        {(props) => (
          <SettingsScreen
            {...props}
            theme={theme}
            themeName={theme.name}
            onThemeChange={themeActions.setThemeName}
          />
        )}
      </Drawer.Screen>
      <Drawer.Screen name="Notifications" options={{ title: 'Notifications' }}>
        {(props) => <NotificationsScreen {...props} session={session} theme={theme} />}
      </Drawer.Screen>
      <Drawer.Screen
        name="BookingChat"
        options={{
          title: 'Booking Chat',
          drawerItemStyle: { display: 'none' },
        }}
      >
        {(props) => <BookingChatScreen {...props} session={session} theme={theme} />}
      </Drawer.Screen>
    </Drawer.Navigator>
  );
}

export default function AppNavigator({ session, authActions, theme, themeActions }) {
  if (!session) {
    return <AuthStack authActions={authActions} />;
  }

  return <AppDrawer session={session} authActions={authActions} theme={theme} themeActions={themeActions} />;
}

const styles = StyleSheet.create({
  menuButton: {
    marginLeft: 14,
    width: 36,
    height: 36,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'rgba(255,255,255,0.08)',
  },
  drawerStyle: {
    width: 286,
    backgroundColor: '#101317',
  },
  drawerShell: {
    flex: 1,
  },
  drawerScrollContent: {
    paddingTop: 14,
  },
  profileWrap: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 18,
    paddingBottom: 20,
    marginBottom: 8,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(255,255,255,0.08)',
  },
  avatarCircle: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: '#1d9bf0',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  avatarText: {
    color: '#f8fafc',
    fontSize: 22,
    fontWeight: '800',
  },
  profileTextWrap: {
    flex: 1,
  },
  helloText: {
    color: '#f8fafc',
    fontSize: 28,
    fontWeight: '700',
    lineHeight: 32,
  },
  nameText: {
    color: '#cbd5e1',
    fontSize: 13,
    marginTop: 1,
  },
  emailText: {
    color: '#64748b',
    fontSize: 12,
    marginTop: 2,
  },
  roleText: {
    color: '#94a3b8',
    fontSize: 10,
    marginTop: 4,
    letterSpacing: 0.7,
    fontWeight: '700',
  },
  drawerItem: {
    borderRadius: 12,
    marginHorizontal: 10,
    marginVertical: 3,
  },
  drawerLabel: {
    fontSize: 17,
    fontWeight: '600',
    marginLeft: -8,
  },
  logoutWrap: {
    borderTopWidth: 1,
    borderTopColor: 'rgba(255,255,255,0.08)',
    paddingTop: 8,
    paddingBottom: 20,
  },
  logoutItem: {
    borderRadius: 12,
    marginHorizontal: 10,
  },
  logoutLabel: {
    color: '#e2e8f0',
    fontSize: 17,
    fontWeight: '600',
    marginLeft: -8,
  },
});
