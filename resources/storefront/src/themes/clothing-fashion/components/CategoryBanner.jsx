import React from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight } from 'lucide-react';

export default function CategoryBanner() {
  return (
    <section className="py-8 md:py-12">
      <div className="container mx-auto px-4">
        <Link
          to="/category/jackets"
          className="group relative block overflow-hidden bg-gray-900"
        >
          <div
            className="h-[250px] md:h-[350px] bg-cover bg-center bg-gray-900"
            style={{ backgroundImage: 'url(https://images.unsplash.com/photo-1551028719-00167b16eac5?w=1920&q=80)' }}
          />
          <div className="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-transparent" />
          <div className="absolute inset-0 flex items-center">
            <div className="px-6 md:px-12 text-white max-w-lg">
              <span className="text-xs md:text-sm uppercase tracking-[0.2em] text-white/60">Limited Edition</span>
              <h2 className="text-2xl md:text-4xl font-bold mt-2 mb-3">Winter Collection</h2>
              <p className="text-sm md:text-base text-white/70 mb-4">Up to 50% off on jackets & coats</p>
              <span className="inline-flex items-center gap-2 text-sm font-medium border-b border-white pb-0.5 group-hover:gap-3 transition-all">
                Shop Collection
                <ArrowRight className="w-4 h-4" />
              </span>
            </div>
          </div>
        </Link>
      </div>
    </section>
  );
}
