import React, { useState } from 'react';
import { Send } from 'lucide-react';
import { NewsletterSkeleton } from '../../../components/shared/SectionSkeletons';

export default function Newsletter({ loading }) {
  const [email, setEmail] = useState('');
  const [submitted, setSubmitted] = useState(false);

  if (loading) return <NewsletterSkeleton />;

  const handleSubmit = (e) => {
    e.preventDefault();
    if (email.trim()) {
      setSubmitted(true);
      setEmail('');
    }
  };

  return (
    <section className="py-12 bg-gray-50">
      <div className="container mx-auto px-4">
        <div className="max-w-xl mx-auto text-center">
          <h2 className="text-2xl font-bold mb-4">Stay Updated</h2>
          <p className="text-gray-600 mb-6">Subscribe to our newsletter for the latest products and offers.</p>

          {submitted ? (
            <div className="bg-green-50 text-green-700 p-4 rounded-lg">Thank you for subscribing!</div>
          ) : (
            <form onSubmit={handleSubmit} className="flex gap-2">
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="Enter your email"
                required
                className="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-gray-900"
              />
              <button type="submit" className="bg-gray-900 text-white px-6 py-3 rounded-lg hover:bg-gray-800 transition flex items-center gap-2">
                <Send className="w-4 h-4" />
                <span className="hidden sm:inline">Subscribe</span>
              </button>
            </form>
          )}
        </div>
      </div>
    </section>
  );
}
