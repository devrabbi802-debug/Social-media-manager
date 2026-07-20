import React from 'react';
import { Link } from 'react-router-dom';
import { ArrowUpRight } from 'lucide-react';

const topGrid = [
  { col: 'md:col-start-1', row: 'md:row-start-1 md:row-end-3', tall: true },
  { col: 'md:col-start-2', row: 'md:row-start-1', tall: false },
  { col: 'md:col-start-2', row: 'md:row-start-2', tall: false },
  { col: 'md:col-start-3', row: 'md:row-start-1 md:row-end-3', tall: true },
];

export default function CategoryGrid({ categories = [] }) {
  if (categories.length === 0) return null;

  const top = categories.slice(0, 4);
  const bottom = categories.slice(4);

  return (
    <section className="w-full pt-[2px]">
      <div className="grid grid-cols-2 md:grid-cols-3 md:grid-rows-2 gap-[2px] h-screen max-h-[900px]">
        {top.map((category, index) => {
          const g = topGrid[index];
          return (
            <Link
              key={category.id}
              to={`/category/${category.slug}`}
              className={`group relative block overflow-hidden ${g.col} ${g.row}`}
            >
              <div className="h-full bg-cover bg-center" style={{ backgroundImage: `url(${category.image})` }}>
                <div className="absolute inset-0 bg-black/10 group-hover:bg-black/30 transition-all duration-500" />
                <div className="absolute inset-0 flex flex-col items-center justify-center p-4 text-white">
                  <h3 className="font-bold text-sm md:text-base text-center tracking-wider uppercase">
                    {category.name}
                  </h3>
                  <div className="mt-2 flex items-center gap-1 text-xs text-white/80 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <span>Shop Now</span>
                    <ArrowUpRight className="w-3 h-3" />
                  </div>
                </div>
              </div>
            </Link>
          );
        })}
      </div>

      {bottom.length > 0 && (
        <div className="grid grid-cols-2 gap-[2px] pt-[2px]">
          {bottom.map((category) => (
            <Link
              key={category.id}
              to={`/category/${category.slug}`}
              className="group relative block overflow-hidden"
            >
              <div className="h-full bg-cover bg-center min-h-[200px] md:min-h-[250px]" style={{ backgroundImage: `url(${category.image})` }}>
                <div className="absolute inset-0 bg-black/10 group-hover:bg-black/30 transition-all duration-500" />
                <div className="absolute inset-0 flex flex-col items-center justify-center p-4 text-white">
                  <h3 className="font-bold text-sm md:text-base text-center tracking-wider uppercase">
                    {category.name}
                  </h3>
                  <div className="mt-2 flex items-center gap-1 text-xs text-white/80 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <span>Shop Now</span>
                    <ArrowUpRight className="w-3 h-3" />
                  </div>
                </div>
              </div>
            </Link>
          ))}
        </div>
      )}
    </section>
  );
}
