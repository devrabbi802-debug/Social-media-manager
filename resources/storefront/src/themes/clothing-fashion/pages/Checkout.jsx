import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { CreditCard, Truck, ShieldCheck, CheckCircle, UserPlus } from 'lucide-react';
import { useCart } from '../contexts/CartContext';
import { useAuth } from '../../../contexts/AuthContext';
import api from '../../../api/client';

export default function Checkout() {
  const { items, subtotal, clearCart } = useCart();
  const { isAuthenticated, register } = useAuth();
  const navigate = useNavigate();

  const [showRegister, setShowRegister] = useState(false);
  const [regEmail, setRegEmail] = useState('');
  const [regPassword, setRegPassword] = useState('');

  const [form, setForm] = useState({
    name: '',
    phone: '',
    address: '',
    city: '',
    district: '',
    zip: '',
    payment_method: 'COD',
  });
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(null);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const placeOrder = async () => {
    const payload = {
      items: items.map((item) => ({
        product_id: item.product_id,
        variant_id: item.variant_id,
        name: item.name,
        unit_price: item.unit_price,
        quantity: item.quantity,
      })),
      payment_method: form.payment_method,
    };

    if (form.name) {
      payload.shipping_address = {
        name: form.name,
        phone: form.phone,
        address: form.address,
        city: form.city,
        district: form.district,
        zip: form.zip,
      };
    }

    const res = await api.post('/checkout/place', payload);
    return res.order;
  };

  const handleGuestOrder = async () => {
    setError('');
    if (items.length === 0) { setError('Your cart is empty.'); return; }
    setSubmitting(true);
    try {
      const order = await placeOrder();
      clearCart();
      setSuccess(order);
    } catch (err) {
      setError(err.response?.data?.message || 'Order failed. Please try again.');
    } finally {
      setSubmitting(false);
    }
  };

  const handleRegisterAndOrder = async () => {
    setError('');
    if (!regEmail || !regPassword) { setError('Email and password required.'); return; }
    setSubmitting(true);
    try {
      await register({ email: regEmail, password: regPassword, password_confirmation: regPassword });
      const order = await placeOrder();
      clearCart();
      setSuccess(order);
    } catch (err) {
      const data = err.response?.data;
      setError(data?.errors?.email?.[0] || data?.message || 'Registration failed.');
    } finally {
      setSubmitting(false);
    }
  };

  if (success) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center max-w-md mx-auto px-4">
          <div className="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <CheckCircle className="w-8 h-8 text-green-600" />
          </div>
          <h1 className="text-2xl font-bold mb-2">Order Placed!</h1>
          <p className="text-gray-500 text-sm mb-6">Order #{success.order_number}</p>
          <Link to="/" className="bg-gray-900 text-white px-6 py-2.5 text-sm font-medium hover:bg-gray-800 transition">
            Continue Shopping
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div>
      <div className="container mx-auto px-4 py-8">
        {/* Register prompt - upore option */}
        {!isAuthenticated && !showRegister && (
          <div className="mb-6 border border-gray-100 rounded-lg p-4 flex items-center justify-between gap-3">
            <div className="flex items-center gap-2 text-sm text-gray-600">
              <UserPlus className="w-4 h-4 text-gray-400" />
              <span>Want to track orders easily?</span>
            </div>
            <button
              type="button"
              onClick={() => setShowRegister(true)}
              className="text-xs text-gray-900 underline hover:no-underline font-medium whitespace-nowrap"
            >
              Create an account
            </button>
          </div>
        )}

        {/* Register form - toggled */}
        {!isAuthenticated && showRegister && (
          <div className="mb-6 border border-gray-200 rounded-lg p-5 bg-gray-50">
            <div className="flex items-center justify-between mb-3">
              <h3 className="text-sm font-bold flex items-center gap-2">
                <UserPlus className="w-4 h-4" />
                Create Account & Order
              </h3>
              <button
                type="button"
                onClick={() => setShowRegister(false)}
                className="text-xs text-gray-400 hover:text-gray-900"
              >
                Skip, order as guest
              </button>
            </div>
            <div className="grid grid-cols-2 gap-3 mb-3">
              <input
                type="email" value={regEmail} onChange={(e) => setRegEmail(e.target.value)}
                placeholder="Email" required
                className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900"
              />
              <input
                type="password" value={regPassword} onChange={(e) => setRegPassword(e.target.value)}
                placeholder="Password (min 6)" required minLength={6}
                className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900"
              />
            </div>
            <button
              type="button"
              onClick={handleRegisterAndOrder}
              disabled={submitting}
              className="w-full bg-gray-900 text-white py-2.5 text-sm font-medium hover:bg-gray-800 transition disabled:opacity-50 flex items-center justify-center gap-2"
            >
              {submitting ? <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" /> : <UserPlus className="w-4 h-4" />}
              {submitting ? 'Processing...' : 'Register & Place Order'}
            </button>
          </div>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-5 gap-8">
          <div className="lg:col-span-3">
            <div className="border border-gray-100 p-6 md:p-8">
              <h2 className="text-lg font-bold mb-6">Shipping Details</h2>
              <div className="mb-4">
                <label className="text-xs text-gray-500 mb-1 block">Full Name</label>
                <input name="name" value={form.name} onChange={handleChange} placeholder="Your name" className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
              </div>
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <label className="text-xs text-gray-500 mb-1 block">Phone</label>
                  <input name="phone" value={form.phone} onChange={handleChange} className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
                </div>
                <div>
                  <label className="text-xs text-gray-500 mb-1 block">City</label>
                  <input name="city" value={form.city} onChange={handleChange} className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
                </div>
              </div>
              <div className="mb-4">
                <label className="text-xs text-gray-500 mb-1 block">Address</label>
                <input name="address" value={form.address} onChange={handleChange} className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-xs text-gray-500 mb-1 block">District</label>
                  <input name="district" value={form.district} onChange={handleChange} className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
                </div>
                <div>
                  <label className="text-xs text-gray-500 mb-1 block">ZIP Code</label>
                  <input name="zip" value={form.zip} onChange={handleChange} className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
                </div>
              </div>

              {/* Payment */}
              <div className="border-t border-gray-100 pt-6 mt-8">
                <h2 className="text-lg font-bold mb-4">Payment Method</h2>
                <div className="space-y-3">
                  <label className="flex items-center gap-3 p-4 border border-gray-200 cursor-pointer hover:border-gray-900 transition">
                    <input type="radio" name="payment_method" value="COD" checked={form.payment_method === 'COD'} onChange={handleChange} className="accent-gray-900" />
                    <Truck className="w-5 h-5 text-gray-400" />
                    <div>
                      <span className="text-sm font-medium">Cash on Delivery</span>
                      <p className="text-xs text-gray-400">Pay when you receive</p>
                    </div>
                  </label>
                  <label className="flex items-center gap-3 p-4 border border-gray-200 cursor-pointer hover:border-gray-900 transition">
                    <input type="radio" name="payment_method" value="SSLCOMMERZ" checked={form.payment_method === 'SSLCOMMERZ'} onChange={handleChange} className="accent-gray-900" />
                    <CreditCard className="w-5 h-5 text-gray-400" />
                    <div>
                      <span className="text-sm font-medium">SSLCOMMERZ</span>
                      <p className="text-xs text-gray-400">Cards & MFS</p>
                    </div>
                  </label>
                </div>
              </div>

              <div className="flex items-center gap-2 text-xs text-gray-400 mt-6">
                <ShieldCheck className="w-4 h-4" />
                Your information is secure and encrypted
              </div>
            </div>
          </div>

          {/* Order Summary Sidebar */}
          <div className="lg:col-span-2">
            <div className="border border-gray-100 p-6 sticky top-28">
              <h3 className="text-sm font-bold uppercase tracking-wider mb-4">Order Summary</h3>

              <div className="space-y-3 mb-4">
                {items.map((item) => (
                  <div key={item.product_id} className="flex gap-3">
                    <img src={item.image || 'https://placehold.co/56x56?text=Item'} alt={item.name} className="w-14 h-14 bg-gray-50 object-cover flex-shrink-0" />
                    <div className="flex-1 min-w-0">
                      <h4 className="text-xs font-medium text-gray-900 line-clamp-1">{item.name}</h4>
                      <p className="text-xs text-gray-400">× {item.quantity}</p>
                      <span className="text-xs font-bold">৳{item.unit_price * item.quantity}</span>
                    </div>
                  </div>
                ))}
              </div>

              <div className="space-y-2 pt-4 border-t border-gray-100 text-sm">
                <div className="flex justify-between text-gray-500">
                  <span>Subtotal</span>
                  <span>৳{subtotal.toLocaleString()}</span>
                </div>
                <div className="flex justify-between text-gray-500">
                  <span>Shipping</span>
                  <span className="text-green-600">Free</span>
                </div>
                <div className="flex justify-between font-bold text-base pt-2 border-t border-gray-100">
                  <span>Total</span>
                  <span>৳{subtotal.toLocaleString()}</span>
                </div>
              </div>

              {error && <p className="text-xs text-red-500 mt-3">{error}</p>}

              <button
                type="button"
                onClick={handleGuestOrder}
                disabled={submitting}
                className="w-full bg-gray-900 text-white py-3 text-sm font-medium hover:bg-gray-800 transition uppercase tracking-wider mt-6 disabled:opacity-50 flex items-center justify-center gap-2"
              >
                {submitting ? <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" /> : null}
                {submitting ? 'Placing Order...' : 'Place Order'}
              </button>

              <Link to="/cart" className="block text-center text-xs text-gray-400 mt-3 hover:text-gray-900 transition">
                Back to Cart
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
