import React, { useState } from 'react';
import { Facebook, Instagram, Youtube, Linkedin } from 'lucide-react';

export default function Footer() {
  const [email, setEmail] = useState('');
  const [subscribed, setSubscribed] = useState(false);

  const handleSubscribe = (e) => {
    e.preventDefault();
    if (email.trim()) {
      setSubscribed(true);
      setEmail('');
    }
  };

  return (
    <footer className="bg-gray-900 text-white">
      <div className="container mx-auto px-4 py-16">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-10 md:gap-8">
          <div className="md:col-span-2 lg:col-span-1">
            <h3 className="text-lg font-bold mb-4 tracking-tight">ONE UMMAH BD</h3>
            <p className="text-sm text-gray-400 leading-relaxed mb-4">
              Partner with One Ummah BD through our profit-sharing opportunities designed with AAOIFI (Accounting and Auditing Organization for Islamic Financial Institutions) standard clarity and trust.
            </p>
            <div className="flex items-center gap-3 mt-6">
              <a href="#" target="_blank" rel="noopener noreferrer" className="w-9 h-9 flex items-center justify-center bg-white/10 hover:bg-blue-600 transition">
                <Facebook className="w-4 h-4" />
              </a>
              <a href="#" target="_blank" rel="noopener noreferrer" className="w-9 h-9 flex items-center justify-center bg-white/10 hover:bg-pink-600 transition">
                <Instagram className="w-4 h-4" />
              </a>
              <a href="#" target="_blank" rel="noopener noreferrer" className="w-9 h-9 flex items-center justify-center bg-white/10 hover:bg-red-600 transition">
                <Youtube className="w-4 h-4" />
              </a>
              <a href="#" target="_blank" rel="noopener noreferrer" className="w-9 h-9 flex items-center justify-center bg-white/10 hover:bg-blue-700 transition">
                <Linkedin className="w-4 h-4" />
              </a>
            </div>
          </div>

          <div>
            <h4 className="text-sm font-bold uppercase tracking-wider mb-5">Quick Links</h4>
            <ul className="space-y-3">
              <li><a href="#" className="text-sm text-gray-400 hover:text-white transition">Store Locator</a></li>
              <li><a href="#" className="text-sm text-gray-400 hover:text-white transition">Security</a></li>
              <li><a href="#" className="text-sm text-gray-400 hover:text-white transition">Fundify.xyz</a></li>
              <li><a href="#" className="text-sm text-gray-400 hover:text-white transition">Blogs</a></li>
            </ul>
          </div>

          <div>
            <h4 className="text-sm font-bold uppercase tracking-wider mb-5">Legal</h4>
            <ul className="space-y-3">
              <li><a href="#" className="text-sm text-gray-400 hover:text-white transition">Return and Exchange</a></li>
              <li><a href="#" className="text-sm text-gray-400 hover:text-white transition">Terms & Conditions</a></li>
              <li><a href="#" className="text-sm text-gray-400 hover:text-white transition">Privacy Policy</a></li>
            </ul>
          </div>

          <div>
            <h4 className="text-sm font-bold uppercase tracking-wider mb-5">Sign Up for Email</h4>
            <p className="text-sm text-gray-400 mb-4 leading-relaxed">
              Sign up to get first dibs on new arrivals, sales, exclusive content, events and more!
            </p>
            {subscribed ? (
              <p className="text-sm text-green-400">Thank you for subscribing!</p>
            ) : (
              <form onSubmit={handleSubscribe} className="flex border-b border-gray-600 pb-1">
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="Enter email address"
                  required
                  className="flex-1 bg-transparent text-sm text-white placeholder-gray-500 py-2 focus:outline-none"
                />
                <button type="submit" className="text-sm font-medium hover:text-gray-300 transition whitespace-nowrap">
                  Subscribe
                </button>
              </form>
            )}
          </div>
        </div>
      </div>

      <div className="border-t border-gray-800">
        <div className="container mx-auto px-4 py-6 text-center text-sm text-gray-500">
          © 2026 One Ummah BD. All rights reserved.
        </div>
      </div>
    </footer>
  );
}
