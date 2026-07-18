import { useState, useEffect } from 'react';
import api from '../api/client';

export function useTheme() {
  const [theme, setTheme] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchTheme = async () => {
      try {
        setLoading(true);
        const response = await api.get('/storefront/config');
        setTheme(response.theme?.config || response.data?.theme?.config);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchTheme();
  }, []);

  return { theme, loading, error };
}