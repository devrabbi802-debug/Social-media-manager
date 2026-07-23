import React from 'react';
import { Link } from 'react-router-dom';
import { Trash2, Minus, Plus, ShoppingBag, ArrowLeft } from 'lucide-react';
import { useCart } from '../contexts/CartContext';

export default function Cart() {
  const { items, updateQuantity, removeItem, subtotal, itemCount } = useCart();

  return (
    <div>
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-between mb-8">
          <h1 className="text-xl md:text-2xl font-bold tracking-tight">Shopping Cart</h1>
          <span className="text-sm text-gray-400">{itemCount} items</span>
        </div>

        {items.length === 0 ? (
          <div className="text-center py-20">
            <ShoppingBag className="w-16 h-16 text-gray-200 mx-auto mb-4" />
            <h2 className="text-lg font-medium text-gray-600 mb-2">Your cart is empty</h2>
            <Link to="/products" className="inline-flex items-center gap-1 text-sm text-gray-900 border-b border-gray-900 pb-0.5 hover:gap-2 transition-all">
              Continue Shopping
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div className="lg:col-span-2 space-y-4">
              {items.map((item) => (
                <div key={item.product_id} className="flex gap-4 p-4 border border-gray-100">
                  <Link to={`/products/${item.slug}`} className="w-24 h-24 md:w-28 md:h-28 bg-gray-50 flex-shrink-0">
                    <img src={item.image || 'https://placehold.co/112x112?text=Item'} alt={item.name} className="w-full h-full object-cover" />
                  </Link>
                  <div className="flex-1 min-w-0">
                    <div className="flex justify-between items-start mb-1">
                      <Link to={`/products/${item.slug}`}>
                        <h3 className="text-sm font-medium text-gray-900 hover:text-gray-600 transition line-clamp-1">
                          {item.name}
                        </h3>
                      </Link>
                      <span className="text-sm font-bold text-gray-900 ml-2 whitespace-nowrap">৳{item.unit_price * item.quantity}</span>
                    </div>
                    <div className="flex items-center justify-between mt-3">
                      <div className="flex items-center border border-gray-200">
                        <button
                          onClick={() => updateQuantity(item.product_id, -1)}
                          className="w-8 h-8 flex items-center justify-center hover:bg-gray-50 transition"
                        >
                          <Minus className="w-3 h-3" />
                        </button>
                        <span className="w-8 h-8 flex items-center justify-center text-xs font-medium border-x border-gray-200">
                          {item.quantity}
                        </span>
                        <button
                          onClick={() => updateQuantity(item.product_id, 1)}
                          className="w-8 h-8 flex items-center justify-center hover:bg-gray-50 transition"
                        >
                          <Plus className="w-3 h-3" />
                        </button>
                      </div>
                      <button
                        onClick={() => removeItem(item.product_id)}
                        className="text-xs text-gray-400 hover:text-red-500 transition flex items-center gap-1"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                        Remove
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>

            <div className="lg:col-span-1">
              <div className="border border-gray-100 p-6 sticky top-28">
                <h3 className="text-sm font-bold uppercase tracking-wider mb-4">Order Summary</h3>

                <div className="space-y-3 mb-4 pb-4 border-b border-gray-100">
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-500">Subtotal</span>
                    <span className="font-medium">৳{subtotal.toLocaleString()}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-500">Shipping</span>
                    <span className="text-green-600 font-medium">Free</span>
                  </div>
                </div>

                <div className="flex justify-between text-base font-bold mb-6">
                  <span>Total</span>
                  <span>৳{subtotal.toLocaleString()}</span>
                </div>

                <Link
                  to="/checkout"
                  className="block w-full text-center bg-gray-900 text-white py-3 text-sm font-medium hover:bg-gray-800 transition uppercase tracking-wider"
                >
                  Checkout
                </Link>

                <Link
                  to="/products"
                  className="block text-center text-xs text-gray-400 mt-4 hover:text-gray-900 transition flex items-center justify-center gap-1"
                >
                  <ArrowLeft className="w-3 h-3" />
                  Continue Shopping
                </Link>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
