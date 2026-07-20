import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { CreditCard, Truck, ShieldCheck, ChevronRight } from 'lucide-react';

const cartItems = [
  { id: 1, name: 'Premium Cotton Oversized T-Shirt', slug: 'premium-cotton-oversized-tshirt', image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=200&q=80', price: 1299, quantity: 2, size: 'L', color: 'Black' },
  { id: 2, name: 'Slim Fit Denim Jeans', slug: 'slim-fit-denim-jeans', image: 'https://images.unsplash.com/photo-1542272454315-4c01d7abdf4a?w=200&q=80', price: 1899, quantity: 1, size: '32', color: 'Blue' },
];

export default function Checkout() {
  const [step, setStep] = useState(1);
  const [form, setForm] = useState({
    firstName: '', lastName: '', email: '', phone: '',
    address: '', city: '', district: '', zip: '',
  });

  const subtotal = cartItems.reduce((sum, item) => sum + item.price * item.quantity, 0);
  const total = subtotal;

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  return (
    <div className="pt-20">
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center gap-2 mb-8 text-xs text-gray-400 uppercase tracking-wider">
          <span className={step >= 1 ? 'text-gray-900 font-medium' : ''}>Shipping</span>
          <ChevronRight className="w-3 h-3" />
          <span className={step >= 2 ? 'text-gray-900 font-medium' : ''}>Payment</span>
          <ChevronRight className="w-3 h-3" />
          <span className={step >= 3 ? 'text-gray-900 font-medium' : ''}>Review</span>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-5 gap-8">
          <div className="lg:col-span-3">
            <div className="border border-gray-100 p-6 md:p-8">
              <h2 className="text-lg font-bold mb-6">Shipping Information</h2>
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <label className="text-xs text-gray-500 mb-1 block">First Name</label>
                  <input name="firstName" value={form.firstName} onChange={handleChange} className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
                </div>
                <div>
                  <label className="text-xs text-gray-500 mb-1 block">Last Name</label>
                  <input name="lastName" value={form.lastName} onChange={handleChange} className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <label className="text-xs text-gray-500 mb-1 block">Email</label>
                  <input name="email" type="email" value={form.email} onChange={handleChange} className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
                </div>
                <div>
                  <label className="text-xs text-gray-500 mb-1 block">Phone</label>
                  <input name="phone" type="tel" value={form.phone} onChange={handleChange} className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
                </div>
              </div>
              <div className="mb-4">
                <label className="text-xs text-gray-500 mb-1 block">Address</label>
                <input name="address" value={form.address} onChange={handleChange} className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
              </div>
              <div className="grid grid-cols-3 gap-4 mb-4">
                <div>
                  <label className="text-xs text-gray-500 mb-1 block">City</label>
                  <input name="city" value={form.city} onChange={handleChange} className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
                </div>
                <div>
                  <label className="text-xs text-gray-500 mb-1 block">District</label>
                  <input name="district" value={form.district} onChange={handleChange} className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
                </div>
                <div>
                  <label className="text-xs text-gray-500 mb-1 block">ZIP Code</label>
                  <input name="zip" value={form.zip} onChange={handleChange} className="w-full border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
                </div>
              </div>

              <div className="border-t border-gray-100 pt-6 mt-8">
                <h2 className="text-lg font-bold mb-4">Payment Method</h2>
                <div className="space-y-3">
                  <label className="flex items-center gap-3 p-4 border border-gray-200 cursor-pointer hover:border-gray-900 transition">
                    <input type="radio" name="payment" defaultChecked className="accent-gray-900" />
                    <CreditCard className="w-5 h-5 text-gray-400" />
                    <div>
                      <span className="text-sm font-medium">SSLCOMMERZ</span>
                      <p className="text-xs text-gray-400">Cards & MFS</p>
                    </div>
                  </label>
                  <label className="flex items-center gap-3 p-4 border border-gray-200 cursor-pointer hover:border-gray-900 transition">
                    <input type="radio" name="payment" className="accent-gray-900" />
                    <Truck className="w-5 h-5 text-gray-400" />
                    <div>
                      <span className="text-sm font-medium">Cash on Delivery</span>
                      <p className="text-xs text-gray-400">Pay when you receive</p>
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

          <div className="lg:col-span-2">
            <div className="border border-gray-100 p-6 sticky top-28">
              <h3 className="text-sm font-bold uppercase tracking-wider mb-4">Order Summary</h3>

              <div className="space-y-3 mb-4">
                {cartItems.map((item) => (
                  <div key={item.id} className="flex gap-3">
                    <img src={item.image} alt={item.name} className="w-14 h-14 bg-gray-50 object-cover" />
                    <div className="flex-1 min-w-0">
                      <h4 className="text-xs font-medium text-gray-900 line-clamp-1">{item.name}</h4>
                      <p className="text-xs text-gray-400">{item.color} / {item.size} × {item.quantity}</p>
                      <span className="text-xs font-bold">৳{item.price * item.quantity}</span>
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
                  <span>৳{total.toLocaleString()}</span>
                </div>
              </div>

              <button className="w-full bg-gray-900 text-white py-3 text-sm font-medium hover:bg-gray-800 transition uppercase tracking-wider mt-6">
                Place Order
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
