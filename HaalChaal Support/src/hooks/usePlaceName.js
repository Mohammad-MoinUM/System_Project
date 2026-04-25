import { useEffect, useState } from 'react';

const placeCache = new Map();

export function usePlaceName(lat, lng) {
  const [placeName, setPlaceName] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (lat === null || lat === undefined || lng === null || lng === undefined) {
      setPlaceName(null);
      setLoading(false);
      return;
    }

    const normalizedLat = Number(lat);
    const normalizedLng = Number(lng);
    const cacheKey = `${normalizedLat},${normalizedLng}`;
    const cachedValue = placeCache.get(cacheKey);

    if (cachedValue) {
      setPlaceName(cachedValue);
      setLoading(false);
      return;
    }

    let isActive = true;
    setLoading(true);

    fetch(
      `https://nominatim.openstreetmap.org/reverse?lat=${normalizedLat}&lon=${normalizedLng}&format=json`,
      { headers: { 'Accept-Language': 'en' } }
    )
      .then((response) => response.json())
      .then((data) => {
        const city = data.address?.city || data.address?.town || data.address?.village || '';
        const country = data.address?.country || '';
        const short = city && country ? `${city}, ${country}` : (data.display_name || `${normalizedLat}, ${normalizedLng}`);
        const nextValue = {
          short,
          full: data.display_name || `${normalizedLat}, ${normalizedLng}`,
        };

        placeCache.set(cacheKey, nextValue);

        if (isActive) {
          setPlaceName(nextValue);
        }
      })
      .catch(() => {
        const fallbackValue = {
          short: `${normalizedLat}, ${normalizedLng}`,
          full: `${normalizedLat}, ${normalizedLng}`,
        };

        if (isActive) {
          setPlaceName(fallbackValue);
        }
      })
      .finally(() => {
        if (isActive) {
          setLoading(false);
        }
      });

    return () => {
      isActive = false;
    };
  }, [lat, lng]);

  return { placeName, loading };
}
