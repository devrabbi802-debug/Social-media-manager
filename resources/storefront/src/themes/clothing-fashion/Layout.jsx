import React, { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { CartProvider } from './contexts/CartContext';
import { WishlistProvider } from './contexts/WishlistContext';
import Header from './components/Header';
import Footer from './components/Footer';
import CartDrawer from './components/CartDrawer';

const notices = [
  '🚚 Free Shipping on orders over ৳1500',
  '🎉 Summer Sale — Up to 40% Off',
  '📦 Cash on Delivery Available',
  '✨ New Arrivals Added Weekly',
];

function ScrollToTop() {
  const { pathname } = useLocation();
  useEffect(() => { window.scrollTo(0, 0); }, [pathname]);
  return null;
}

function NoticeBar() {
  return (
    <div id="store-notice-bar" className="fixed top-0 left-0 right-0 z-[60] h-8 bg-gray-900 overflow-hidden">
      <div className="absolute inset-0 flex items-center whitespace-nowrap ticker-track">
        {[...Array(3)].flatMap(() => notices).map((text, i) => (
          <span key={i} className="inline-block text-white text-[11px] uppercase tracking-[0.2em] font-medium px-8">
            {text}
          </span>
        ))}
      </div>
      <style>{`
        .ticker-track {
          animation: ticker 40s linear infinite;
        }
        @keyframes ticker {
          0% { transform: translateX(0); }
          100% { transform: translateX(-50%); }
        }
      `}</style>
    </div>
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
        <main className="flex-1">
          {children}
        </main>
        <Footer />
        <CartDrawer />
      </div>
      </WishlistProvider>
    </CartProvider>
  );
}
