import { useEffect, useRef } from 'react';
import * as Location from 'expo-location';
import { apiRequest } from '../services/api';

export function useProviderTracking(token, bookingId, isActive) {
  const intervalRef = useRef(null);

  useEffect(() => {
    if (!isActive || !bookingId || !token) return undefined;

    let cancelled = false;

    const startTracking = async () => {
      const { status } = await Location.requestForegroundPermissionsAsync();

      if (status !== 'granted' || cancelled) return;

      intervalRef.current = setInterval(async () => {
        try {
          const loc = await Location.getCurrentPositionAsync({
            accuracy: Location.Accuracy.High,
          });

          await apiRequest(`/bookings/${bookingId}/location`, {
            method: 'POST',
            token,
            body: {
              latitude: loc.coords.latitude,
              longitude: loc.coords.longitude,
            },
          });
        } catch {
          // Ignore intermittent location/network failures and try again on the next tick.
        }
      }, 3000);
    };

    startTracking().catch(() => {});

    return () => {
      cancelled = true;

      if (intervalRef.current) {
        clearInterval(intervalRef.current);
        intervalRef.current = null;
      }
    };
  }, [bookingId, isActive, token]);
}