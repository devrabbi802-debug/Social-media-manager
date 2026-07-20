import React, { useState } from 'react';
import ProductCard from './ProductCard';

export default function ProductSection({ title, products = [], initialCount = 8 }) {
  const [showAll, setShowAll] = useState(false);
  const displayProducts = showAll ? products : products.slice(0, initialCount);

  if (products.length === 0) return null;

  return (
    <section className="py-8 md:py-12">
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between mb-6 md:mb-8">
          <div className="flex items-center gap-3">
            <div className="w-1 h-6 bg-gray-900" />
            <h2 className="text-lg md:text-2xl font-bold tracking-tight">{title}</h2>
          </div>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
          {displayProducts.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>

        {products.length > initialCount && (
          <div className="flex justify-center mt-8">
            <button
              onClick={() => setShowAll(!showAll)}
              className="px-8 py-2.5 border border-gray-900 text-gray-900 text-sm font-medium hover:bg-gray-900 hover:text-white transition tracking-wider uppercase"
            >
              {showAll ? 'Show Less' : `Load More (${products.length - initialCount})`}
            </button>
          </div>
        )}
      </div>
    </section>
  );
}
