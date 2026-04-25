import React, { useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, SafeAreaView, StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';
import { Feather } from '@expo/vector-icons';
import { fetchChatThread, sendSupportMessage } from '../services/api';

export default function ChatScreen({ session, theme }) {
  const [message, setMessage] = useState('');
  const [messages, setMessages] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const screenTheme = theme || {
    background: '#090b10',
    card: '#101826',
    text: '#f8fafc',
    muted: '#d1d5db',
    border: '#1f2937',
    accent: '#fcd34d',
    accentSoft: 'rgba(252, 211, 77, 0.14)',
  };

  useEffect(() => {
    let active = true;

    fetchChatThread(session?.token)
      .then((payload) => {
        if (!active) {
          return;
        }

        setMessages(payload.messages || []);
        setLoading(false);
      })
      .catch((exception) => {
        if (!active) {
          return;
        }

        setError(exception.message || 'Failed to load chat thread.');
        setLoading(false);
      });

    return () => {
      active = false;
    };
  }, [session?.token]);

  const submitMessage = async () => {
    if (!message.trim()) {
      return;
    }

    try {
      const sent = await sendSupportMessage(session?.token, { message: message.trim() });
      setMessages((current) => [...current, sent.item]);
      setMessage('');
    } catch (exception) {
      setError(exception.message || 'Unable to send message.');
    }
  };

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: screenTheme.background }]}>
      <View style={styles.glowTop} />
      <View style={styles.container}>
        <View style={[styles.headerCard, { backgroundColor: screenTheme.card, borderColor: screenTheme.border }]}>
          <View style={styles.headerTop}>
            <View style={styles.headerIconWrap}>
              <Feather name="message-circle" size={16} color={screenTheme.background} />
            </View>
            <Text style={[styles.headerTag, { color: screenTheme.accent }]}>always on</Text>
          </View>
          <Text style={[styles.title, { color: screenTheme.text }]}>Support Chat</Text>
          <Text style={[styles.subtitle, { color: screenTheme.muted }]}>Send fast updates to support while handling active service tasks.</Text>
        </View>

        {loading ? (
          <View style={styles.loadingBox}>
            <ActivityIndicator color={screenTheme.accent} />
            <Text style={[styles.loadingText, { color: screenTheme.muted }]}>Loading chat...</Text>
          </View>
        ) : null}
        {error ? <Text style={styles.error}>{error}</Text> : null}

        <FlatList
          data={messages}
          keyExtractor={(item) => item.id}
          contentContainerStyle={styles.list}
          style={styles.listSurface}
          renderItem={({ item }) => (
            <View
              style={[
                styles.bubble,
                { borderColor: screenTheme.border },
                item.is_mine ? { backgroundColor: screenTheme.accentSoft, alignSelf: 'flex-end' } : { backgroundColor: screenTheme.card, alignSelf: 'flex-start' },
              ]}
            >
              <Text style={[styles.sender, { color: screenTheme.muted }, item.is_mine && { color: screenTheme.accent }]}>{item.sender_name || 'Support'}</Text>
              <Text style={[styles.message, { color: screenTheme.text }]}>{item.message}</Text>
            </View>
          )}
        />

        <View style={styles.inputRow}>
          <TextInput
            value={message}
            onChangeText={setMessage}
            placeholder="Type your message"
            placeholderTextColor={screenTheme.muted}
            style={styles.input}
          />
          <TouchableOpacity onPress={submitMessage} style={[styles.button, { backgroundColor: screenTheme.accent }]}>
            <Feather name="send" size={15} color="#111827" />
            <Text style={styles.buttonText}>Send</Text>
          </TouchableOpacity>
        </View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1, backgroundColor: '#090b10' },
  glowTop: {
    position: 'absolute',
    top: -110,
    right: -100,
    width: 250,
    height: 250,
    borderRadius: 125,
    backgroundColor: 'rgba(252, 211, 77, 0.14)',
  },
  container: { flex: 1, padding: 20 },
  headerCard: {
    backgroundColor: '#101826',
    borderRadius: 22,
    padding: 16,
    borderWidth: 1,
    borderColor: '#1f2937',
    marginBottom: 12,
  },
  headerTop: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 8 },
  headerIconWrap: {
    width: 34,
    height: 34,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#fcd34d',
  },
  headerTag: {
    color: '#fde68a',
    textTransform: 'uppercase',
    letterSpacing: 0.8,
    fontSize: 11,
    fontWeight: '800',
  },
  title: { fontSize: 28, fontWeight: '900', color: '#f8fafc', marginBottom: 6 },
  subtitle: { color: '#d1d5db', fontSize: 13, lineHeight: 19 },
  loadingBox: { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 8 },
  loadingText: { color: '#e2e8f0' },
  error: { color: '#fca5a5', marginBottom: 8, fontWeight: '700' },
  listSurface: {
    flex: 1,
    borderRadius: 20,
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    paddingHorizontal: 10,
  },
  list: { paddingBottom: 14, paddingTop: 12 },
  bubble: { borderRadius: 16, padding: 14, marginBottom: 10, maxWidth: '88%' },
  supportBubble: { backgroundColor: '#e2e8f0', alignSelf: 'flex-start', borderTopLeftRadius: 6 },
  userBubble: { backgroundColor: '#fef3c7', alignSelf: 'flex-end', borderTopRightRadius: 6 },
  sender: { fontSize: 12, fontWeight: '800', color: '#334155', marginBottom: 4 },
  senderMine: { color: '#92400e' },
  message: { fontSize: 14, lineHeight: 20, color: '#0f172a' },
  inputRow: { flexDirection: 'row', gap: 10, paddingTop: 10 },
  input: {
    flex: 1,
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 14,
    paddingHorizontal: 14,
    paddingVertical: 12,
    color: '#0f172a',
  },
  button: {
    backgroundColor: '#fcd34d',
    borderRadius: 14,
    paddingHorizontal: 14,
    justifyContent: 'center',
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  buttonText: { color: '#111827', fontWeight: '800' },
});
