import React, { useState, useEffect, Suspense } from 'react';
import { BrowserRouter, Routes, Route, useSearchParams } from 'react-router-dom';
import { ThemeProvider } from './contexts/ThemeContext';
import { loadTheme } from './themes';
import { EditorProvider, useEditor } from './components/editor/EditorContext';
import EditorToolbar from './components/editor/EditorToolbar';
import BannerEditorModal from './components/editor/BannerEditorModal';
import ChunkErrorBoundary from './components/shared/ChunkErrorBoundary';
import api from './api/client';

function EditorOverlay({ onExit }) {
  const { activeSection, closeEditor } = useEditor();

  return (
    <>
      <EditorToolbar onExit={onExit} />
      {activeSection?.type === 'banners' && (
        <BannerEditorModal
          sectionData={activeSection.data}
          onClose={closeEditor}
          onSaved={(banners) => {
            window.__editor_banners = banners;
          }}
        />
      )}
    </>
  );
}

function AppContent() {
  const [searchParams, setSearchParams] = useSearchParams();
  const isEditorMode = searchParams.get('editor') === 'true';

  const exitEditor = () => {
    const params = new URLSearchParams(searchParams);
    params.delete('editor');
    params.delete('theme');
    setSearchParams(params, { replace: true });
    window.location.reload();
  };

  return (
    <EditorProvider isEditorMode={isEditorMode}>
      <RouterContent isEditorMode={isEditorMode} onExitEditor={exitEditor} />
    </EditorProvider>
  );
}

function RouterContent({ isEditorMode, onExitEditor }) {
  const [config, setConfig] = useState(null);
  const [themeComponents, setThemeComponents] = useState(null);

  useEffect(() => {
    const init = async () => {
      try {
        const cfg = window.__STOREFRONT_DATA__ || await api.get('/storefront/config');
        setConfig(cfg);

        const urlTheme = new URLSearchParams(window.location.search).get('theme');
        const slug = urlTheme || cfg?.theme_slug || cfg?.theme?.slug || 'clothing-fashion';
        const theme = await loadTheme(slug);
        setThemeComponents(theme);
      } catch (err) {
        const theme = await loadTheme('clothing-fashion');
        setThemeComponents(theme);
        setConfig({ store_name: 'Store', theme: null, theme_slug: 'clothing-fashion' });
      }
    };
    init();
  }, []);

  useEffect(() => {
    if (themeComponents) {
      const shell = document.getElementById('app-shell');
      if (shell) shell.style.display = 'none';
    }
  }, [themeComponents]);

  if (!themeComponents) return null;

  const {
    Layout, Home, Products, ProductDetail, Category, Brand, Cart, Checkout, Auth, NotFound,
    DashboardLayout, DashboardHome, DashboardOrders, DashboardTracking,
    DashboardWishlist, DashboardAddresses, DashboardSettings,
  } = themeComponents;

  return (
    <ThemeProvider
      initialConfig={config?.theme?.config}
      initialSlug={config?.theme_slug || config?.theme?.slug || 'clothing-fashion'}
    >
      <Layout config={config}>
        {isEditorMode && <EditorOverlay onExit={onExitEditor} />}
        <ChunkErrorBoundary onChunkError="reload">
            <Suspense fallback={null}>
              {isEditorMode && <div className="h-[44px]" />}
            <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/products" element={<Products />} />
              <Route path="/products/:slug" element={<ProductDetail />} />
              <Route path="/category/:slug" element={<Category />} />
              <Route path="/brand/:slug" element={<Brand />} />
              <Route path="/cart" element={<Cart />} />
              <Route path="/checkout" element={<Checkout />} />
              <Route path="/auth" element={<Auth />} />
              <Route element={<DashboardLayout />}>
                <Route path="/dashboard" element={<DashboardHome />} />
                <Route path="/dashboard/orders" element={<DashboardOrders />} />
                <Route path="/dashboard/tracking" element={<DashboardTracking />} />
                <Route path="/dashboard/wishlist" element={<DashboardWishlist />} />
                <Route path="/dashboard/addresses" element={<DashboardAddresses />} />
                <Route path="/dashboard/settings" element={<DashboardSettings />} />
              </Route>
              <Route path="*" element={<NotFound />} />
            </Routes>
          </Suspense>
        </ChunkErrorBoundary>
      </Layout>
    </ThemeProvider>
  );
}

export default function App() {
  return (
    <BrowserRouter>
      <AppContent />
    </BrowserRouter>
  );
}
