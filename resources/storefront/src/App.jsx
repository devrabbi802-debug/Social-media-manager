import React, { useState, useEffect, Suspense } from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { ThemeProvider } from './contexts/ThemeContext';
import { loadTheme } from './themes';
import LoadingSpinner from './components/shared/LoadingSpinner';
import api from './api/client';

export default function App() {
  const [config, setConfig] = useState(null);
  const [loading, setLoading] = useState(true);
  const [themeComponents, setThemeComponents] = useState(null);

  useEffect(() => {
    const init = async () => {
      try {
        let cfg;
        if (window.__STOREFRONT_DATA__) {
          cfg = window.__STOREFRONT_DATA__;
        } else {
          cfg = await api.get('/storefront/config');
        }
        setConfig(cfg);

        const slug = cfg?.theme_slug || cfg?.theme?.slug || 'clothing-fashion';
        const theme = await loadTheme(slug);
        setThemeComponents(theme);
      } catch (err) {
        console.error('Failed to initialize:', err);
        const theme = await loadTheme('clothing-fashion');
        setThemeComponents(theme);
        setConfig({ store_name: 'Store', theme: null, theme_slug: 'clothing-fashion' });
      } finally {
        setLoading(false);
      }
    };
    init();
  }, []);

  if (loading || !themeComponents) {
    return <LoadingSpinner />;
  }

  const { Layout, Home, Products, ProductDetail, Category, Brand, NotFound } = themeComponents;

  return (
    <ThemeProvider
      initialConfig={config?.theme?.config}
      initialSlug={config?.theme_slug || config?.theme?.slug || 'clothing-fashion'}
    >
      <BrowserRouter>
        <Layout config={config}>
          <Suspense fallback={<LoadingSpinner />}>
            <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/products" element={<Products />} />
              <Route path="/products/:slug" element={<ProductDetail />} />
              <Route path="/category/:slug" element={<Category />} />
              <Route path="/brand/:slug" element={<Brand />} />
              <Route path="*" element={<NotFound />} />
            </Routes>
          </Suspense>
        </Layout>
      </BrowserRouter>
    </ThemeProvider>
  );
}
