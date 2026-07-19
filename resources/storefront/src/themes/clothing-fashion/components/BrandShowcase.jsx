import React from 'react';
import { Link } from 'react-router-dom';

export default function BrandShowcase({ brands = [] }) {
  if (brands.length === 0) return null;

  return (
    <section className="py-12 bg-surface">
      <div className="container mx-auto px-4">
        <h2 className="text-2xl font-bold text-center mb-8">Our Brands</h2>
        <div className="flex flex-wrap justify-center items-center gap-8">
          {brands.map((brand) => (
            <Link key={brand.id} to={`/brand/${brand.slug}`} className="group">
              <div className="bg-white rounded-lg p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                {brand.logo ? (
                  <img src={`/storage/${brand.logo}`} alt={brand.name} className="h-12 w-auto object-contain group-hover:scale-105 transition" />
                ) : (
                  <span className="text-lg font-medium text-gray-700 group-hover:text-primary transition">{brand.name}</span>
                )}
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}
