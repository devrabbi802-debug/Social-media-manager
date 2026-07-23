import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { CategorySliderSkeleton } from '../../../components/shared/SectionSkeletons';

export default function CategorySlider({ categories = [], loading }) {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [visibleCount, setVisibleCount] = useState(5);

  useEffect(() => {
    const update = () => setVisibleCount(window.innerWidth < 768 ? 2 : 5);
    update();
    window.addEventListener('resize', update);
    return () => window.removeEventListener('resize', update);
  }, []);

  if (loading) return <CategorySliderSkeleton />;
  if (categories.length === 0) return null;

  const maxIndex = Math.max(0, categories.length - visibleCount);
  const canGoLeft = currentIndex > 0;
  const canGoRight = currentIndex < maxIndex;

  const visibleCategories = categories.slice(currentIndex, currentIndex + visibleCount);

  const goLeft = () => setCurrentIndex((prev) => Math.max(0, prev - 1));
  const goRight = () => setCurrentIndex((prev) => Math.min(maxIndex, prev + 1));

  return (
    <section className="py-8 md:py-12">
      <div className="container mx-auto px-4">
        <h2 className="text-lg md:text-2xl font-bold tracking-tight text-center mb-6 md:mb-8">All Categories</h2>

        <div className="relative">
          {canGoLeft && (
            <button
              onClick={goLeft}
              className="absolute -left-3 md:left-0 top-1/2 -translate-y-1/2 z-[61] w-10 h-10 bg-white shadow-lg border border-gray-100 flex items-center justify-center hover:bg-gray-50 transition"
            >
              <ChevronLeft className="w-5 h-5 text-gray-700" />
            </button>
          )}

          <div className="overflow-hidden mx-0 md:mx-2">
            <div
              className="flex gap-3 transition-transform duration-500 ease-in-out"
              style={{ transform: `translateX(0)` }}
            >
              {visibleCategories.map((cat) => (
                <Link
                  key={cat.id}
                  to={`/category/${cat.slug}`}
                  className="group flex-1 min-w-0"
                >
                  <div className="relative bg-gray-900 border border-gray-100 hover:border-gray-300 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div className="aspect-[4/5] bg-cover bg-center group-hover:scale-105 transition duration-500" style={{ backgroundImage: `url(${cat.custom_image || cat.image})` }} />
                    <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
                    <div className="absolute bottom-0 left-0 right-0 p-3 text-center">
                      <h3 className="text-sm font-semibold text-white drop-shadow-sm">{cat.name}</h3>
                      {cat.products_count && (
                        <p className="text-[10px] text-white/70 mt-0.5">{cat.products_count} items</p>
                      )}
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          </div>

          {canGoRight && (
            <button
              onClick={goRight}
              className="absolute -right-3 md:right-0 top-1/2 -translate-y-1/2 z-[61] w-10 h-10 bg-white shadow-lg border border-gray-100 flex items-center justify-center hover:bg-gray-50 transition"
            >
              <ChevronRight className="w-5 h-5 text-gray-700" />
            </button>
          )}
        </div>

        <div className="flex items-center justify-center gap-1.5 mt-5">
          {Array.from({ length: maxIndex + 1 }).map((_, i) => (
            <button
              key={i}
              onClick={() => setCurrentIndex(i)}
              className={`transition-all duration-300 rounded-full ${
                i === currentIndex ? 'w-6 h-2 bg-gray-900' : 'w-2 h-2 bg-gray-300 hover:bg-gray-400'
              }`}
            />
          ))}
        </div>
      </div>
    </section>
  );
}
