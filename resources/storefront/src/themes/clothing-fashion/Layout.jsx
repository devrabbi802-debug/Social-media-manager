import React, { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
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

export default function Layout({ children, config }) {
  return (
    <CartProvider>
      <WishlistProvider>
      <ScrollToTop />
      <div className="min-h-screen flex flex-col">
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
