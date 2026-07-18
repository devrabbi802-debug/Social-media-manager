import React from 'react';
import Header from './Header';
import Footer from './Footer';

export default function Layout({ children, config }) {
  return (
    <div className="min-h-screen flex flex-col">
      <Header
        storeName={config?.store_name}
        storeLogo={config?.store_logo}
        categories={config?.categories || []}
      />
      <main className="flex-1">
        {children}
      </main>
      <Footer config={config} />
    </div>
  );
}