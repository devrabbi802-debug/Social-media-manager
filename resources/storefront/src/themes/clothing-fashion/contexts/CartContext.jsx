import React, { createContext, useContext, useState, useCallback } from 'react';

const CartContext = createContext(null);

const initialItems = [
  { id: 1, name: 'Premium Cotton Oversized T-Shirt', slug: 'premium-cotton-oversized-tshirt', image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=200&q=80', price: 1299, quantity: 2, size: 'L', color: 'Black' },
  { id: 2, name: 'Slim Fit Denim Jeans', slug: 'slim-fit-denim-jeans', image: 'https://images.unsplash.com/photo-1542272454315-4c01d7abdf4a?w=200&q=80', price: 1899, quantity: 1, size: '32', color: 'Blue' },
];

export function CartProvider({ children }) {
  const [items, setItems] = useState(initialItems);
  const [drawerOpen, setDrawerOpen] = useState(false);

  const openDrawer = useCallback(() => setDrawerOpen(true), []);
  const closeDrawer = useCallback(() => setDrawerOpen(false), []);

  const addToCart = useCallback((product) => {
    setItems((prev) => {
      const existing = prev.find((item) => item.id === product.id);
      if (existing) {
        return prev.map((item) =>
          item.id === product.id ? { ...item, quantity: item.quantity + 1 } : item
        );
      }
      return [
        ...prev,
        {
          id: product.id,
          name: product.name,
          slug: product.slug,
          image: product.image,
          price: product.effective_price || product.price,
          quantity: 1,
          size: product.size || 'M',
          color: product.color || 'Black',
        },
      ];
    });
    setDrawerOpen(true);
  }, []);

  const updateQuantity = useCallback((id, delta) => {
    setItems((prev) =>
      prev.map((item) =>
        item.id === id ? { ...item, quantity: Math.max(1, item.quantity + delta) } : item
      )
    );
  }, []);

  const removeItem = useCallback((id) => {
    setItems((prev) => prev.filter((item) => item.id !== id));
  }, []);

  const itemCount = items.reduce((sum, item) => sum + item.quantity, 0);

  return (
    <CartContext.Provider value={{ items, drawerOpen, openDrawer, closeDrawer, addToCart, updateQuantity, removeItem, itemCount }}>
      {children}
    </CartContext.Provider>
  );
}

export function useCart() {
  const context = useContext(CartContext);
  if (!context) throw new Error('useCart must be used within CartProvider');
  return context;
}
