import React, { createContext, useContext, useState, useCallback, useEffect } from 'react';
import { useAuth } from '../../../contexts/AuthContext';
import api from '../../../api/client';

const WishlistContext = createContext(null);

export function WishlistProvider({ children }) {
  const { isAuthenticated } = useAuth();
  const [wishlist, setWishlist] = useState([]);
  const [loading, setLoading] = useState(false);
  const [showLoginPopup, setShowLoginPopup] = useState(false);

  useEffect(() => {
    if (!isAuthenticated) {
      setWishlist([]);
      return;
    }
    setLoading(true);
    api.get('/customer/wishlist').then((data) => {
      if (Array.isArray(data)) {
        setWishlist(data.map((p) => ({
          id: p.id,
          name: p.name,
          slug: p.slug,
          image: p.image,
          price: p.effective_price || p.price || p.base_price,
        })));
      }
    }).catch(() => {}).finally(() => setLoading(false));
  }, [isAuthenticated]);

  const toggleWishlist = useCallback(async (product) => {
    if (!isAuthenticated) {
      setShowLoginPopup(true);
      return;
    }

    const exists = wishlist.find((item) => item.id === product.id);

    if (exists) {
      try {
        await api.delete(`/customer/wishlist/${product.id}`);
        setWishlist((prev) => prev.filter((item) => item.id !== product.id));
      } catch {}
    } else {
      try {
        await api.post('/customer/wishlist', { product_id: product.id });
        setWishlist((prev) => [...prev, {
          id: product.id,
          name: product.name,
          slug: product.slug,
          image: product.image,
          price: product.effective_price || product.price || product.base_price,
        }]);
      } catch {}
    }
  }, [isAuthenticated, wishlist]);

  const isWishlisted = useCallback((id) => wishlist.some((item) => item.id === id), [wishlist]);

  return (
    <WishlistContext.Provider value={{ wishlist, loading, toggleWishlist, isWishlisted }}>
      {children}

      {showLoginPopup && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
          onClick={() => setShowLoginPopup(false)}
        >
          <div
            className="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 relative animate-fade-in"
            onClick={(e) => e.stopPropagation()}
          >
            <button
              onClick={() => setShowLoginPopup(false)}
              className="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition"
            >
              <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>
            </button>

            <div className="text-center mb-6">
              <div className="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-7 h-7 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
              </div>
              <h2 className="text-lg font-bold text-gray-900 mb-1.5">Login Required</h2>
              <p className="text-sm text-gray-500">Please log in to save items to your wishlist.</p>
            </div>

            <div className="space-y-2.5">
              <button
                onClick={() => { window.location.href = '/auth'; }}
                className="w-full bg-gray-900 text-white py-3 rounded-xl text-sm font-semibold hover:bg-gray-800 transition active:scale-[0.98]"
              >
                Log In
              </button>
              <button
                onClick={() => { window.location.href = '/auth?tab=register'; }}
                className="w-full border border-gray-200 text-gray-700 py-3 rounded-xl text-sm font-semibold hover:border-gray-900 hover:text-gray-900 transition active:scale-[0.98]"
              >
                Create Account
              </button>
              <button
                onClick={() => setShowLoginPopup(false)}
                className="w-full text-xs text-gray-400 py-2 hover:text-gray-600 transition"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}
    </WishlistContext.Provider>
  );
}

export function useWishlist() {
  const context = useContext(WishlistContext);
  if (!context) throw new Error('useWishlist must be used within WishlistProvider');
  return context;
}
