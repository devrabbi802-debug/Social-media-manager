import React from 'react';
import { Link } from 'react-router-dom';
import ProductCard from '../ui/ProductCard';

export default function FeaturedProducts({ products = [] }) {
  if (products.length === 0) {
    return null;
  }

  return (
    <section className="py-12">
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between mb-8">
          <h2 className="text-2xl font-bold">Featured Products</h2>
          <Link
            to="/products"
            className="text-primary hover:text-primary/80 font-medium transition"
          >
            View All →
          </Link>
        </div>
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
          {products.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      </div>
    </section>
  );
}