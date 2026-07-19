import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import ProductCard from '../components/ProductCard';
import api from '../../../api/client';

function ProductCardSkeleton() {
  return (
    <div className="bg-white border border-gray-200">
      <div className="aspect-square animate-pulse bg-gray-100" />
      <div className="p-4">
        <div className="h-4 bg-gray-200 rounded w-1/3 mb-2 animate-pulse" />
        <div className="h-6 bg-gray-200 rounded w-full mb-2 animate-pulse" />
        <div className="h-4 bg-gray-200 rounded w-2/3 animate-pulse" />
      </div>
    </div>
  );
}

export default function Products() {
  const [searchParams, setSearchParams] = useSearchParams();
  const [data, setData] = useState({ data: [], meta: {} });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const currentPage = parseInt(searchParams.get('page') || '1');
  const sort = searchParams.get('sort') || 'newest';
  const category = searchParams.get('category') || '';
  const brand = searchParams.get('brand') || '';

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        setLoading(true);
        const params = new URLSearchParams();
        params.set('page', currentPage);
        params.set('sort', sort);
        if (category) params.set('category', category);
        if (brand) params.set('brand', brand);
        const response = await api.get(`/storefront/products?${params.toString()}`);
        setData(response);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };
    fetchProducts();
  }, [currentPage, sort, category, brand]);

  const handleSortChange = (newSort) => {
    searchParams.set('sort', newSort);
    searchParams.set('page', '1');
    setSearchParams(searchParams);
  };

  const handlePageChange = (page) => {
    searchParams.set('page', page.toString());
    setSearchParams(searchParams);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  if (error) {
    return (
      <div className="container mx-auto px-4 py-12 text-center">
        <p className="text-red-500">Error loading products: {error}</p>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <h1 className="text-2xl font-bold text-gray-900">
          {category ? `${category} Products` : brand ? `${brand} Products` : 'All Products'}
        </h1>
        <select
          value={sort}
          onChange={(e) => handleSortChange(e.target.value)}
          className="border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
        >
          <option value="newest">Newest First</option>
          <option value="price_asc">Price: Low to High</option>
          <option value="price_desc">Price: High to Low</option>
        </select>
      </div>

      {loading ? (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
          {[...Array(12)].map((_, i) => <ProductCardSkeleton key={i} />)}
        </div>
      ) : data.data.length === 0 ? (
        <div className="text-center py-12">
          <p className="text-gray-500 text-lg">No products found.</p>
        </div>
      ) : (
        <>
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            {data.data.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>

          {data.meta && data.meta.last_page > 1 && (
            <div className="flex justify-center items-center gap-2 mt-8">
              {Array.from({ length: data.meta.last_page }, (_, i) => i + 1).map((page) => (
                <button
                  key={page}
                  onClick={() => handlePageChange(page)}
                  className={`px-4 py-2 transition ${
                    page === data.meta.current_page
                      ? 'bg-primary text-white'
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  }`}
                >
                  {page}
                </button>
              ))}
            </div>
          )}
        </>
      )}
    </div>
  );
}
