import React from 'react';
import { Link } from 'react-router-dom';
import { CategoryGridSkeleton } from '../../../components/shared/SectionSkeletons';

function CategoryCard({ category }) {
  return (
    <Link
      to={`/category/${category.slug}`}
      className="group relative block overflow-hidden bg-gray-900 min-h-[200px] h-full"
    >
      <div
        className="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105"
        style={{ backgroundImage: `url(${category.custom_image || category.image})` }}
      />
      <div className="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-all duration-500" />
      <div className="absolute inset-0 flex flex-col items-center justify-end p-4 md:p-6 text-white bg-gradient-to-t from-black/60 via-transparent to-transparent">
        <h3 className="font-bold text-sm md:text-base text-center tracking-wider uppercase drop-shadow-sm">
          {category.name}
        </h3>
        {category.products_count && (
          <p className="text-[10px] text-white/70 mt-0.5">{category.products_count} Products</p>
        )}
      </div>
    </Link>
  );
}

export default function CategoryGrid({ categories = [], loading }) {
  if (loading) return <CategoryGridSkeleton />;
  if (categories.length === 0) return null;

  const [c1, c2, c3, c4, c5] = categories;

  return (
    <section className="px-4 pt-4 md:pt-6 pb-2">
      <div className="flex flex-col md:flex-row gap-[2px] md:h-[600px]">
        <div className="flex flex-col gap-[2px] flex-1">
          {c1 && <CategoryCard category={c1} />}
          {c2 && <CategoryCard category={c2} />}
        </div>
        <div className="flex-1 md:flex-[1.5]">
          {c3 && <CategoryCard category={c3} />}
        </div>
        <div className="flex flex-col gap-[2px] flex-1">
          {c4 && <CategoryCard category={c4} />}
          {c5 && <CategoryCard category={c5} />}
        </div>
      </div>
    </section>
  );
}
