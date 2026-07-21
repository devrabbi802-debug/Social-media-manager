import React from 'react';
import ProductCard from './ProductCard';
import { ProductGridSkeleton } from '../../../components/shared/SectionSkeletons';

export default function CategoryProducts({ title, products = [], loading }) {
  if (loading) return <ProductGridSkeleton />;
  if (products.length === 0) return null;

  return (
    <section className="py-8 md:py-12 bg-gray-50">
      <div className="container mx-auto px-4">
        <h2 className="text-lg md:text-2xl font-bold tracking-tight text-center mb-6 md:mb-8">{title}</h2>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
          {products.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      </div>
    </section>
  );
}
