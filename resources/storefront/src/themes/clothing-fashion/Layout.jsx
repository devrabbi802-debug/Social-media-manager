import React, { useEffect, useState } from 'react';
import { useLocation } from 'react-router-dom';
import { Edit3 } from 'lucide-react';
import { useEditor } from '../../components/editor/EditorContext';
import NoticeEditorModal from '../../components/editor/NoticeEditorModal';
import { CartProvider } from './contexts/CartContext';
import { WishlistProvider } from './contexts/WishlistContext';
import Header from './components/Header';
import Footer from './components/Footer';
import CartDrawer from './components/CartDrawer';

function ScrollToTop() {
  const { pathname } = useLocation();
  useEffect(() => { window.scrollTo(0, 0); }, [pathname]);
  return null;
}

function NoticeBar() {
  const { isEditorMode } = useEditor();
  const [isHovered, setIsHovered] = useState(false);
  const [showEditor, setShowEditor] = useState(false);
  const [notices, setNotices] = useState(null);

  useEffect(() => {
    if (window.__editor_notices !== undefined) setNotices(window.__editor_notices);
    const handler = () => setNotices(window.__editor_notices ?? []);
    window.addEventListener('notices:updated', handler);
    return () => window.removeEventListener('notices:updated', handler);
  }, []);

  return (
    <>
    <div
      id="store-notice-bar"
      className="fixed top-0 left-0 right-0 z-[60] h-8 bg-gray-900 overflow-hidden"
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      {notices === null ? (
        <div className="absolute inset-0 bg-gray-800 animate-pulse" />
      ) : (
        <div className="absolute inset-0 flex items-center whitespace-nowrap ticker-track">
          {[...Array(20)].flatMap(() => notices).map((text, i) => (
            <span key={i} className="inline-block text-white text-[11px] uppercase tracking-[0.2em] font-medium px-8">
              {text}
            </span>
          ))}
        </div>
      )}
      <style>{`
        .ticker-track {
          animation: ticker 40s linear infinite;
        }
        @keyframes ticker {
          0% { transform: translateX(0); }
          100% { transform: translateX(-50%); }
        }
      `}</style>

      {isEditorMode && isHovered && (
        <div className="absolute top-0 right-0 h-full z-[61] flex items-center pr-2">
          <button
            onClick={(e) => { e.stopPropagation(); setShowEditor(true); }}
            className="w-6 h-6 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white/40 transition border border-white/30"
          >
            <Edit3 className="w-3 h-3 text-white" />
          </button>
        </div>
      )}
    </div>

    {showEditor && (
      <NoticeEditorModal
        sectionData={{ notices }}
        onClose={() => setShowEditor(false)}
      />
    )}
    </>
  );
}

export default function Layout({ children, config }) {
  return (
    <CartProvider>
      <WishlistProvider>
      <ScrollToTop />
      <NoticeBar />
      <div className="min-h-screen flex flex-col pt-8 bg-white">
        <Header
          storeName={config?.store_name}
          storeLogo={config?.store_logo}
        />
        <main className="flex-1 pt-16">
          {children}
        </main>
        <Footer />
        <CartDrawer />
      </div>
      </WishlistProvider>
    </CartProvider>
  );
}
