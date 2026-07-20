import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { Trash2, Minus, Plus, ShoppingBag, ArrowLeft } from 'lucide-react';

const initialItems = [
  {
    id: 1,
    name: 'Premium Cotton Oversized T-Shirt',
    slug: 'premium-cotton-oversized-tshirt',
    image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=200&q=80',
    price: 1299,
    quantity: 2,
    size: 'L',
    color: 'Black',
  },
  {
    id: 2,
    name: 'Slim Fit Denim Jeans',
    slug: 'slim-fit-denim-jeans',
    image: 'https://images.unsplash.com/photo-1542272454315-4c01d7abdf4a?w=200&q=80',
    price: 1899,
    quantity: 1,
    size: '32',
    color: 'Blue',
  },
  {
    id: 3,
    name: 'Running Shoes Pro',
    slug: 'running-shoes-pro',
    image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200&q=80',
    price: 2599,
    quantity: 1,
    size: 'EU 42',
    color: 'White',
  },
];

export default function Cart() {
  const [items, setItems] = useState(initialItems);
  const [couponCode, setCouponCode] = useState('');
  const [couponApplied, setCouponApplied] = useState(false);

  const updateQuantity = (id, delta) => {
    setItems((prev) =>
      prev.map((item) =>
        item.id === id
          ? { ...item, quantity: Math.max(1, item.quantity + delta) }
          : item
      )
    );
  };

  const removeItem = (id) => {
    setItems((prev) => prev.filter((item) => item.id !== id));
  };

  const subtotal = items.reduce((sum, item) => sum + item.price * item.quantity, 0);
  const discount = couponApplied ? subtotal * 0.1 : 0;
  const total = subtotal - discount;

  return (
    <div className="pt-20">
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-between mb-8">
          <h1 className="text-xl md:text-2xl font-bold tracking-tight">Shopping Cart</h1>
          <span className="text-sm text-gray-400">{items.length} items</span>
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
                <div key={item.id} className="flex gap-4 p-4 border border-gray-100">
                  <Link to={`/products/${item.slug}`} className="w-24 h-24 md:w-28 md:h-28 bg-gray-50 flex-shrink-0">
                    <img src={item.image} alt={item.name} className="w-full h-full object-cover" />
                  </Link>
                  <div className="flex-1 min-w-0">
                    <div className="flex justify-between items-start mb-1">
                      <Link to={`/products/${item.slug}`}>
                        <h3 className="text-sm font-medium text-gray-900 hover:text-gray-600 transition line-clamp-1">
                          {item.name}
                        </h3>
                      </Link>
                      <span className="text-sm font-bold text-gray-900 ml-2 whitespace-nowrap">৳{item.price * item.quantity}</span>
                    </div>
                    <p className="text-xs text-gray-400 mb-3">
                      {item.color} / {item.size}
                    </p>
                    <div className="flex items-center justify-between">
                      <div className="flex items-center border border-gray-200">
                        <button
                          onClick={() => updateQuantity(item.id, -1)}
                          className="w-8 h-8 flex items-center justify-center hover:bg-gray-50 transition"
                        >
                          <Minus className="w-3 h-3" />
                        </button>
                        <span className="w-8 h-8 flex items-center justify-center text-xs font-medium border-x border-gray-200">
                          {item.quantity}
                        </span>
                        <button
                          onClick={() => updateQuantity(item.id, 1)}
                          className="w-8 h-8 flex items-center justify-center hover:bg-gray-50 transition"
                        >
                          <Plus className="w-3 h-3" />
                        </button>
                      </div>
                      <button
                        onClick={() => removeItem(item.id)}
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
                  {discount > 0 && (
                    <div className="flex justify-between text-sm">
                      <span className="text-green-600">Discount (10%)</span>
                      <span className="text-green-600">-৳{discount.toLocaleString()}</span>
                    </div>
                  )}
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-500">Shipping</span>
                    <span className="text-green-600 font-medium">Free</span>
                  </div>
                </div>

                <div className="flex justify-between text-base font-bold mb-6">
                  <span>Total</span>
                  <span>৳{total.toLocaleString()}</span>
                </div>

                <div className="mb-4">
                  <div className="flex border border-gray-200">
                    <input
                      type="text"
                      value={couponCode}
                      onChange={(e) => setCouponCode(e.target.value)}
                      placeholder="Coupon code"
                      className="flex-1 px-3 py-2 text-xs focus:outline-none"
                    />
                    <button
                      onClick={() => {
                        if (couponCode.trim()) setCouponApplied(true);
                      }}
                      className="px-4 py-2 bg-gray-900 text-white text-xs font-medium hover:bg-gray-800 transition"
                    >
                      Apply
                    </button>
                  </div>
                  {couponApplied && (
                    <p className="text-xs text-green-600 mt-1">Coupon applied! 10% discount</p>
                  )}
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
