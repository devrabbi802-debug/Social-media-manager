import React from 'react';

export default function PromoBanner() {
  return (
    <section className="py-12">
      <div className="container mx-auto px-4">
        <div className="bg-gradient-to-r from-primary to-secondary rounded-xl p-8 md:p-12 text-white">
          <div className="max-w-2xl">
            <h2 className="text-2xl md:text-3xl font-bold mb-4">
              Special Offer!
            </h2>
            <p className="text-lg mb-6 opacity-90">
              Get up to 50% off on selected items. Limited time offer!
            </p>
            <a
              href="/products"
              className="inline-block bg-white text-primary px-8 py-3 rounded-lg font-medium hover:bg-gray-100 transition"
            >
              Shop Now
            </a>
          </div>
        </div>
      </div>
    </section>
  );
}