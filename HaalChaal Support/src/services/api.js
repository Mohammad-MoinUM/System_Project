import { Platform } from 'react-native';

const API_HOST = 'https://curtly-awry-zesty.ngrok-free.dev';

const API_BASE_URL = `${API_HOST}/api/mobile`;
const REQUEST_TIMEOUT_MS = 12000;

async function apiRequest(path, { method = 'GET', token, body } = {}) {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS);

  try {
    const response = await fetch(`${API_BASE_URL}${path}`, {
      method,
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        "ngrok-skip-browser-warning": "true",
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      body: body ? JSON.stringify(body) : undefined,
      signal: controller.signal,
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(data.message || 'Request failed.');
    }

    return data;
  } catch (error) {
    if (error?.name === 'AbortError') {
      throw new Error('Server timeout. Please try again later.');
    }

    if (error instanceof TypeError) {
      throw new Error('Cannot reach server. ');
    }

    throw error;
  } finally {
    clearTimeout(timeoutId);
  }
}

export { apiRequest };

export function getApiBaseUrl() {
  return API_BASE_URL;
}

export async function signInWithLaravelCredentials({ email, password, role }) {
  const data = await apiRequest('/auth/login', {
    method: 'POST',
    body: { email, password, role },
  });

  return data.session;
}

export async function signUpWithLaravel({ name, email, password, password_confirmation, role, referral_code }) {
  return apiRequest('/auth/register', {
    method: 'POST',
    body: { name, email, password, password_confirmation, role, referral_code },
  });
}

export async function fetchCurrentUser(token) {
  return apiRequest('/auth/me', { token });
}

export async function updateCurrentUserProfile(token, payload) {
  return apiRequest('/auth/profile', {
    method: 'PUT',
    token,
    body: payload,
  });
}

export async function updateCurrentUserPassword(token, payload) {
  return apiRequest('/auth/password', {
    method: 'PUT',
    token,
    body: payload,
  });
}

export async function logout(token) {
  return apiRequest('/auth/logout', {
    method: 'POST',
    token,
  });
}

export async function fetchSupportOverview(token) {
  return apiRequest('/support/overview', { token });
}

export async function fetchPaymentStatus(token) {
  return apiRequest('/support/payments', { token });
}

export async function fetchLiveTrackingStatus(token) {
  return apiRequest('/support/tracking', { token });
}

export async function fetchMyBookings(token) {
  return apiRequest('/support/bookings', { token });
}

export async function startProviderBooking(token, bookingId) {
  return apiRequest(`/support/bookings/${bookingId}/start`, {
    method: 'POST',
    token,
  });
}

export async function requestProviderCompletion(token, bookingId) {
  return apiRequest(`/support/bookings/${bookingId}/request-completion`, {
    method: 'POST',
    token,
  });
}

export async function confirmCustomerCompletion(token, bookingId) {
  return apiRequest(`/support/bookings/${bookingId}/confirm-completion`, {
    method: 'POST',
    token,
  });
}

export async function payCustomerBooking(token, bookingId, payload) {
  return apiRequest(`/support/bookings/${bookingId}/pay`, {
    method: 'POST',
    token,
    body: payload,
  });
}

export async function fetchBookingChatThread(token, bookingId) {
  return apiRequest(`/support/bookings/${bookingId}/chat`, { token });
}

export async function sendBookingChatMessage(token, bookingId, payload) {
  return apiRequest(`/support/bookings/${bookingId}/chat`, {
    method: 'POST',
    token,
    body: payload,
  });
}

export async function tipCustomerBooking(token, bookingId, payload) {
  return apiRequest(`/support/bookings/${bookingId}/tip`, {
    method: 'POST',
    token,
    body: payload,
  });
}

export async function reportCustomerIssue(token, bookingId, payload) {
  return apiRequest(`/support/bookings/${bookingId}/report-issue`, {
    method: 'POST',
    token,
    body: payload,
  });
}

export async function fetchChatThread(token) {
  return apiRequest('/support/chat', { token });
}

export async function sendSupportMessage(token, payload) {
  return apiRequest('/support/chat', {
    method: 'POST',
    token,
    body: payload,
  });
}
