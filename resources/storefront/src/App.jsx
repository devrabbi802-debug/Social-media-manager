import React, { useState, useEffect, Suspense } from 'react';
import { BrowserRouter, Routes, Route, useSearchParams } from 'react-router-dom';
import { ThemeProvider } from './contexts/ThemeContext';
import { loadTheme } from './themes';
import { EditorProvider, useEditor } from './components/editor/EditorContext';
import EditorToolbar from './components/editor/EditorToolbar';
import BannerEditorModal from './components/editor/BannerEditorModal';
import ChunkErrorBoundary from './components/shared/ChunkErrorBoundary';
import Skeleton from './components/shared/Skeleton';
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

function SectionSkeletonBlock({ className }) {
  return (
    <div className={`bg-gray-100 overflow-hidden relative ${className}`}>
      <Skeleton className="absolute inset-0" />
    </div>
  );
}

function PageContentSkeleton() {
  return (
    <div>
      <div className="w-full h-[80vh] min-h-[500px] max-h-[800px] bg-gray-100 overflow-hidden relative">
        <Skeleton className="absolute inset-0" />
        <div className="absolute inset-0 flex flex-col items-center justify-center gap-4 px-6">
          <Skeleton className="h-4 w-32 rounded" />
          <Skeleton className="h-10 w-72 rounded" />
          <Skeleton className="h-5 w-48 rounded" />
          <Skeleton className="h-12 w-36 rounded-full mt-4" />
        </div>
      </div>
      <div className="px-4 pt-4 md:pt-6 pb-2">
        <div className="flex flex-col md:flex-row gap-[2px] md:h-[600px]">
          <div className="flex flex-col gap-[2px] flex-1">
            <SectionSkeletonBlock className="min-h-[200px] h-full mb-[2px]" />
            <SectionSkeletonBlock className="min-h-[200px] h-full" />
          </div>
          <SectionSkeletonBlock className="flex-1 md:flex-[1.5] min-h-[200px] h-full" />
          <div className="flex flex-col gap-[2px] flex-1">
            <SectionSkeletonBlock className="min-h-[200px] h-full mb-[2px]" />
            <SectionSkeletonBlock className="min-h-[200px] h-full" />
          </div>
        </div>
      </div>
      <div className="py-8 md:py-12">
        <div className="container mx-auto px-4">
          <Skeleton className="h-6 w-40 rounded mx-auto mb-8" />
          <div className="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
            {[1,2,3,4].map((i) => (
              <div key={i} className="bg-white">
                <Skeleton className="aspect-square w-full rounded-none" />
                <div className="p-3 space-y-2">
                  <Skeleton className="h-4 w-3/4 rounded" />
                  <Skeleton className="h-3 w-1/2 rounded" />
                  <Skeleton className="h-5 w-1/3 rounded" />
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

function PageShellSkeleton() {
  return (
    <div className="min-h-screen flex flex-col bg-white">
      <div className="h-8 bg-gray-100 overflow-hidden flex items-center px-4">
        <Skeleton className="h-3 w-64 rounded" />
      </div>
      <header className="border-b border-gray-100">
        <div className="container mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-8">
              <Skeleton className="h-8 w-32 rounded" />
              <div className="hidden md:flex items-center gap-6">
                <Skeleton className="h-4 w-16 rounded" />
                <Skeleton className="h-4 w-20 rounded" />
                <Skeleton className="h-4 w-14 rounded" />
                <Skeleton className="h-4 w-24 rounded" />
              </div>
            </div>
            <div className="flex items-center gap-4">
              <Skeleton className="h-9 w-48 rounded-full hidden md:block" />
              <Skeleton className="h-5 w-5 rounded" />
              <Skeleton className="h-5 w-5 rounded" />
            </div>
          </div>
        </div>
      </header>
      <PageContentSkeleton />
      <footer className="bg-gray-900 mt-auto">
        <div className="container mx-auto px-4 py-12">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {[1,2,3,4].map((i) => (
              <div key={i} className="space-y-3">
                <Skeleton className="h-4 w-24 rounded opacity-50" />
                <Skeleton className="h-3 w-32 rounded opacity-30" />
                <Skeleton className="h-3 w-28 rounded opacity-30" />
                <Skeleton className="h-3 w-20 rounded opacity-30" />
              </div>
            ))}
          </div>
        </div>
      </footer>
    </div>
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

  if (!themeComponents) return <PageShellSkeleton />;

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
            <Suspense fallback={<PageContentSkeleton />}>
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
