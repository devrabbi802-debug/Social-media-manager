import React from 'react';
import { Link } from 'react-router-dom';

export default function BrandShowcase({ brands = [] }) {
  if (brands.length === 0) return null;

  return (
    <section className="py-12">
      <div className="container mx-auto px-4">
        <h2 className="text-2xl font-bold text-center mb-8 text-gray-900">Our Brands</h2>
        <div className="flex flex-wrap justify-center items-center gap-6">
          {brands.map((brand) => (
            <Link key={brand.id} to={`/brand/${brand.slug}`} className="group">
              <div className="bg-white border border-gray-200 px-8 py-5 hover:border-primary transition">
                {brand.logo ? (
                  <img src={`/storage/${brand.logo}`} alt={brand.name} className="h-10 w-auto object-contain group-hover:scale-105 transition" />
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
