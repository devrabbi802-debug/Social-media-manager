import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { Heart, Trash2 } from 'lucide-react';
import api from '../../../../api/client';

export default function Wishlist() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchWishlist = async () => {
    try {
      const res = await api.get('/customer/wishlist');
      setItems(res || []);
    } catch {} finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchWishlist(); }, []);

  const removeItem = async (productId) => {
    try {
      await api.delete(`/customer/wishlist/${productId}`);
      setItems((prev) => prev.filter((item) => item.id !== productId));
    } catch {}
  };

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-bold">My Wishlist</h2>
        <span className="text-xs text-gray-400">{items.length} items</span>
      </div>

      {loading ? (
        <div className="flex justify-center py-16">
          <div className="w-8 h-8 border-2 border-gray-900 border-t-transparent rounded-full animate-spin" />
        </div>
      ) : items.length === 0 ? (
        <div className="text-center py-16 border border-gray-100 rounded-lg">
          <Heart className="w-12 h-12 text-gray-200 mx-auto mb-3" />
          <p className="text-gray-400 text-sm mb-3">Your wishlist is empty.</p>
          <Link to="/products" className="text-sm text-gray-900 underline hover:no-underline">
            Browse Products
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {items.map((item) => (
            <div key={item.id} className="group border border-gray-100 rounded-lg overflow-hidden hover:shadow-md transition">
              <Link to={`/products/${item.slug}`} className="block aspect-[3/4] bg-gray-50 relative overflow-hidden">
                <img
                  src={item.image || 'https://placehold.co/300x400?text=No+Image'}
                  alt={item.name}
                  className="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                />
              </Link>
              <div className="p-3">
                <Link to={`/products/${item.slug}`}>
                  <h3 className="text-sm font-medium text-gray-900 line-clamp-1 hover:text-gray-600 transition">
                    {item.name}
                  </h3>
                </Link>
                <div className="flex items-center gap-2 mt-1 mb-2">
                  <span className="text-sm font-bold text-gray-900">৳{item.effective_price || item.base_price}</span>
                  {item.discount_price && (
                    <span className="text-xs text-gray-300 line-through">৳{item.base_price}</span>
                  )}
                </div>
                <button
                  onClick={() => removeItem(item.id)}
                  className="w-full py-2 text-xs font-medium uppercase tracking-wider border border-red-200 text-red-500 hover:bg-red-50 transition flex items-center justify-center gap-1"
                >
                  <Trash2 className="w-3 h-3" />
                  Remove
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
