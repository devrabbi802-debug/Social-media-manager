import React from 'react';
import { Link } from 'react-router-dom';

export default function CategoryGrid({ categories = [] }) {
  if (categories.length === 0) return null;

  return (
    <section className="py-12">
      <div className="container mx-auto px-4">
        <h2 className="text-2xl font-bold text-center mb-8 text-gray-900">Shop by Category</h2>
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          {categories.map((category) => (
            <Link key={category.id} to={`/category/${category.slug}`} className="group">
              <div className="bg-white border border-gray-200 p-4 text-center hover:border-primary transition">
                {category.image ? (
                  <img src={`/storage/${category.image}`} alt={category.name} className="w-16 h-16 mx-auto mb-3 object-contain" />
                ) : (
                  <div className="w-16 h-16 mx-auto mb-3 bg-primary/10 flex items-center justify-center">
                    <span className="text-2xl">📦</span>
                  </div>
                )}
                <h3 className="font-medium text-gray-900 group-hover:text-primary transition">{category.name}</h3>
                {category.products_count !== undefined && (
                  <p className="text-sm text-gray-500 mt-1">{category.products_count} products</p>
                )}
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}
