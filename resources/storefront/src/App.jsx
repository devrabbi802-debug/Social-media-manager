import React, { useState, useEffect } from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { ThemeProvider } from './contexts/ThemeContext';
import Layout from './components/layout/Layout';
import Home from './pages/Home';
import Products from './pages/Products';
import ProductDetail from './pages/ProductDetail';
import Category from './pages/Category';
import Brand from './pages/Brand';
import NotFound from './pages/NotFound';
import api from './api/client';

export default function App() {
  const [config, setConfig] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchConfig = async () => {
      try {
        // Try to get config from server-injected data first
        if (window.__STOREFRONT_DATA__) {
          setConfig(window.__STOREFRONT_DATA__);
          setLoading(false);
          return;
        }

        // Otherwise fetch from API
        const response = await api.get('/storefront/config');
          setConfig(response);
      } catch (err) {
        console.error('Failed to load config:', err);
        // Use default config
        setConfig({
          storeName: 'Store',
          theme: null,
        });
      } finally {
        setLoading(false);
      }
    };

    fetchConfig();
  }, []);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-center">
          <div className="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <p className="text-gray-600">Loading store...</p>
        </div>
      </div>
    );
  }

  return (
    <ThemeProvider initialConfig={config?.theme?.config}>
      <BrowserRouter>
        <Layout config={config}>
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/products" element={<Products />} />
            <Route path="/products/:slug" element={<ProductDetail />} />
            <Route path="/category/:slug" element={<Category />} />
            <Route path="/brand/:slug" element={<Brand />} />
            <Route path="*" element={<NotFound />} />
          </Routes>
        </Layout>
      </BrowserRouter>
    </ThemeProvider>
  );
}