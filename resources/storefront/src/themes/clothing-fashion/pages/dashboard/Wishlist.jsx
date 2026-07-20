import React from 'react';
import { Link } from 'react-router-dom';
import { Heart } from 'lucide-react';
import { wishlistItems } from './data';

export default function DashboardWishlist() {
  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-bold">My Wishlist</h2>
        <span className="text-xs text-gray-400">{wishlistItems.length} items</span>
      </div>

      {wishlistItems.length === 0 ? (
        <div className="text-center py-16 border border-gray-100 rounded-lg">
          <Heart className="w-12 h-12 text-gray-200 mx-auto mb-3" />
          <p className="text-gray-400 text-sm mb-3">Your wishlist is empty.</p>
          <Link to="/products" className="text-sm text-gray-900 underline hover:no-underline">
            Browse Products
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {wishlistItems.map((item) => (
            <div key={item.id} className="group border border-gray-100 rounded-lg overflow-hidden hover:shadow-md transition">
              <Link to={`/products/${item.slug}`} className="block aspect-[3/4] bg-gray-50 relative overflow-hidden">
                <img src={item.image} alt={item.name} className="w-full h-full object-cover group-hover:scale-105 transition duration-500" />
                {!item.inStock && (
                  <span className="absolute top-2 left-2 bg-gray-900 text-white text-[10px] px-2 py-0.5 font-semibold">SOLD OUT</span>
                )}
              </Link>
              <div className="p-3">
                <Link to={`/products/${item.slug}`}>
                  <h3 className="text-sm font-medium text-gray-900 line-clamp-1 hover:text-gray-600 transition">{item.name}</h3>
                </Link>
                <div className="flex items-center gap-2 mt-1 mb-2">
                  <span className="text-sm font-bold text-gray-900">৳{item.effective_price}</span>
                  {item.discount_price && (
                    <span className="text-xs text-gray-300 line-through">৳{item.base_price}</span>
                  )}
                </div>
                <button
                  disabled={!item.inStock}
                  className="w-full py-2 text-xs font-medium uppercase tracking-wider border border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white transition disabled:opacity-40 disabled:cursor-not-allowed"
                >
                  {item.inStock ? 'Add to Cart' : 'Out of Stock'}
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
