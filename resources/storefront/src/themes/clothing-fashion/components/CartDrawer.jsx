import React from 'react';
import { Link } from 'react-router-dom';
import { X, Minus, Plus, Trash2, ShoppingBag } from 'lucide-react';
import { useCart } from '../contexts/CartContext';

export default function CartDrawer() {
  const { items, drawerOpen, closeDrawer, updateQuantity, removeItem } = useCart();
  const subtotal = items.reduce((sum, item) => sum + item.unit_price * item.quantity, 0);

  return (
    <>
      {drawerOpen && (
        <div className="fixed inset-0 bg-black/40 z-50" onClick={closeDrawer} />
      )}

      <div
        className={`fixed top-8 right-0 bottom-0 w-full max-w-md bg-white z-50 shadow-2xl transform transition-transform duration-300 ${
          drawerOpen ? 'translate-x-0' : 'translate-x-full'
        }`}
      >
        <div className="flex flex-col h-full">
          <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 className="text-sm font-bold uppercase tracking-wider">Cart ({items.length})</h2>
            <button onClick={closeDrawer} className="p-1 hover:opacity-70 transition">
              <X className="w-5 h-5" />
            </button>
          </div>

          <div className="flex-1 overflow-y-auto px-5 py-4">
            {items.length === 0 ? (
              <div className="text-center py-16">
                <ShoppingBag className="w-12 h-12 text-gray-200 mx-auto mb-3" />
                <p className="text-sm text-gray-400">Your cart is empty</p>
              </div>
            ) : (
              <div className="space-y-4">
                {items.map((item) => (
                  <div key={item.product_id} className="flex gap-3 pb-4 border-b border-gray-50">
                    <Link to={`/products/${item.slug}`} onClick={closeDrawer} className="w-20 h-20 bg-gray-50 flex-shrink-0">
                      <img src={item.image} alt={item.name} className="w-full h-full object-cover" />
                    </Link>
                    <div className="flex-1 min-w-0">
                      <Link to={`/products/${item.slug}`} onClick={closeDrawer}>
                        <h3 className="text-sm font-medium text-gray-900 line-clamp-1 hover:text-gray-600 transition">{item.name}</h3>
                      </Link>
                      {(item.color || item.size) && (
                        <p className="text-xs text-gray-400 mt-0.5">{[item.color, item.size].filter(Boolean).join(' / ')}</p>
                      )}
                      <p className="text-sm font-bold mt-1">৳{item.unit_price * item.quantity}</p>
                      <div className="flex items-center justify-between mt-2">
                        <div className="flex items-center border border-gray-200">
                          <button onClick={() => updateQuantity(item.product_id, -1)} className="w-7 h-7 flex items-center justify-center hover:bg-gray-50 transition">
                            <Minus className="w-3 h-3" />
                          </button>
                          <span className="w-7 h-7 flex items-center justify-center text-xs font-medium border-x border-gray-200">
                            {item.quantity}
                          </span>
                          <button onClick={() => updateQuantity(item.product_id, 1)} className="w-7 h-7 flex items-center justify-center hover:bg-gray-50 transition">
                            <Plus className="w-3 h-3" />
                          </button>
                        </div>
                        <button onClick={() => removeItem(item.product_id)} className="text-gray-300 hover:text-red-500 transition">
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          <div className="border-t border-gray-100 px-5 py-4 space-y-3">
            <div className="flex justify-between text-sm">
              <span className="text-gray-500">Subtotal</span>
              <span className="font-bold">৳{subtotal.toLocaleString()}</span>
            </div>
            <p className="text-xs text-gray-400">Shipping & taxes calculated at checkout</p>
            <Link
              to="/cart"
              onClick={closeDrawer}
              className="block w-full text-center bg-gray-900 text-white py-3 text-sm font-medium hover:bg-gray-800 transition uppercase tracking-wider"
            >
              View Cart
            </Link>
            <Link
              to="/checkout"
              onClick={closeDrawer}
              className="block w-full text-center border border-gray-900 text-gray-900 py-3 text-sm font-medium hover:bg-gray-50 transition uppercase tracking-wider"
            >
              Checkout
            </Link>
          </div>
        </div>
      </div>
    </>
  );
}
