import React, { createContext, useContext, useState, useCallback, useEffect } from 'react';

const CartContext = createContext(null);

function loadCart() {
  try {
    const saved = localStorage.getItem('storefront_cart');
    return saved ? JSON.parse(saved) : [];
  } catch {
    return [];
  }
}

function saveCart(items) {
  localStorage.setItem('storefront_cart', JSON.stringify(items));
}

export function CartProvider({ children }) {
  const [items, setItems] = useState(loadCart);
  const [drawerOpen, setDrawerOpen] = useState(false);

  useEffect(() => { saveCart(items); }, [items]);

  const openDrawer = useCallback(() => setDrawerOpen(true), []);
  const closeDrawer = useCallback(() => setDrawerOpen(false), []);

  const addToCart = useCallback((product) => {
    setItems((prev) => {
      const existing = prev.find((item) => item.product_id === product.id);
      if (existing) {
        return prev.map((item) =>
          item.product_id === product.id ? { ...item, quantity: item.quantity + 1 } : item
        );
      }
      return [
        ...prev,
        {
          product_id: product.id,
          variant_id: product.variant_id || null,
          name: product.name,
          slug: product.slug,
          image: product.image,
          unit_price: product.effective_price || product.price,
          color: product.color || null,
          size: product.size || null,
          quantity: 1,
        },
      ];
    });
    setDrawerOpen(true);
  }, []);

  const updateQuantity = useCallback((productId, delta) => {
    setItems((prev) =>
      prev.map((item) =>
        item.product_id === productId ? { ...item, quantity: Math.max(1, item.quantity + delta) } : item
      )
    );
  }, []);

  const removeItem = useCallback((productId) => {
    setItems((prev) => prev.filter((item) => item.product_id !== productId));
  }, []);

  const clearCart = useCallback(() => {
    setItems([]);
  }, []);

  const itemCount = items.reduce((sum, item) => sum + item.quantity, 0);
  const subtotal = items.reduce((sum, item) => sum + item.unit_price * item.quantity, 0);

  return (
    <CartContext.Provider value={{
      items, drawerOpen, openDrawer, closeDrawer,
      addToCart, updateQuantity, removeItem, clearCart,
      itemCount, subtotal,
    }}>
      {children}
    </CartContext.Provider>
  );
}

export function useCart() {
  const context = useContext(CartContext);
  if (!context) throw new Error('useCart must be used within CartProvider');
  return context;
}

export default CartContext;
