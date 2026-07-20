import React, { useState, useMemo } from 'react';
import { useParams, Link } from 'react-router-dom';
import ProductCard from '../components/ProductCard';
import { allProducts } from '../data/products';

export default function Brand() {
  const { slug } = useParams();
  const [sort, setSort] = useState('newest');
  const [visibleCount, setVisibleCount] = useState(8);

  const filteredProducts = useMemo(() => {
    let result = allProducts.filter((p) => p.brand.slug === slug);
    if (sort === 'price_asc') result.sort((a, b) => a.effective_price - b.effective_price);
    if (sort === 'price_desc') result.sort((a, b) => b.effective_price - a.effective_price);
    return result;
  }, [slug, sort]);

  const displayedProducts = filteredProducts.slice(0, visibleCount);
  const hasMore = visibleCount < filteredProducts.length;

  return (
    <div className="pt-20">
      <div className="container mx-auto px-4 py-8">
        <nav className="flex items-center gap-2 text-sm text-gray-400 mb-6">
          <Link to="/" className="hover:text-gray-900 transition">Home</Link>
          <span className="text-gray-300">/</span>
          <span className="text-gray-900 capitalize font-medium">{slug}</span>
        </nav>

        <div className="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
          <h1 className="text-2xl font-bold capitalize">{slug}</h1>
          <select
            value={sort}
            onChange={(e) => setSort(e.target.value)}
            className="border border-gray-200 px-4 py-2 text-sm focus:outline-none focus:border-gray-900"
          >
            <option value="newest">Newest</option>
            <option value="price_asc">Price: Low to High</option>
            <option value="price_desc">Price: High to Low</option>
          </select>
        </div>

        {displayedProducts.length === 0 ? (
          <div className="text-center py-12">
            <p className="text-gray-400 text-lg">No products from this brand.</p>
            <Link to="/products" className="text-sm text-gray-900 underline mt-2 inline-block hover:no-underline">
              Browse All Products →
            </Link>
          </div>
        ) : (
          <>
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
              {displayedProducts.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>

            {hasMore && (
              <div className="flex justify-center mt-10">
                <button
                  onClick={() => setVisibleCount((prev) => prev + 8)}
                  className="px-10 py-3 border border-gray-900 text-gray-900 text-sm font-medium hover:bg-gray-900 hover:text-white transition uppercase tracking-wider"
                >
                  Load More ({filteredProducts.length - visibleCount})
                </button>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}
