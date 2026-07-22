import React from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight } from 'lucide-react';
import ProductCard from './ProductCard';
import { ProductGridSkeleton } from '../../../components/shared/SectionSkeletons';

export default function CategoryProducts({ title, data = [], loading }) {
  if (loading) return <ProductGridSkeleton />;
  if (data.length === 0) return null;

  return (
    <section className="py-8 md:py-12 bg-gray-50">
      <div className="container mx-auto px-4">
        <h2 className="text-lg md:text-2xl font-bold tracking-tight text-center mb-6 md:mb-8">{title}</h2>

        <div className="space-y-10">
          {data.map((group) => (
            <div key={group.id || group.slug}>
              {group.banner_image && (
                <Link
                  to={`/category/${group.slug}`}
                  className="group relative block overflow-hidden bg-gray-900 mb-6"
                >
                  <div
                    className="h-[180px] md:h-[260px] bg-cover bg-center bg-gray-900"
                    style={{ backgroundImage: `url(${group.banner_image})` }}
                  />
                  <div className="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-transparent" />
                  <div className="absolute inset-0 flex items-center">
                    <div className="px-6 md:px-10 text-white">
                      <h3 className="text-xl md:text-3xl font-bold">{group.name}</h3>
                      <span className="inline-flex items-center gap-1.5 text-sm font-medium text-white/80 mt-2 group-hover:gap-2.5 transition-all">
                        Shop {group.name}
                        <ArrowRight className="w-4 h-4" />
                      </span>
                    </div>
                  </div>
                </Link>
              )}

              {group.products && group.products.length > 0 && (
                <div className="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
                  {group.products.map((product) => (
                    <ProductCard key={product.id} product={product} />
                  ))}
                </div>
              )}
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
