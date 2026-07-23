import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import ProductCard from '../components/ProductCard';
import api from '../../../api/client';

export default function Category() {
  const { slug } = useParams();
  const [products, setProducts] = useState([]);
  const [categoryName, setCategoryName] = useState(slug);
  const [loading, setLoading] = useState(true);
  const [sort, setSort] = useState('newest');
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);

  useEffect(() => {
    api.get('/storefront/categories').then((data) => {
      if (!Array.isArray(data)) return;
      const findName = (list) => {
        for (const cat of list) {
          if (cat.slug === slug) return cat.name;
          if (cat.children) {
            const found = cat.children.find((c) => c.slug === slug);
            if (found) return found.name;
          }
        }
        return slug;
      };
      setCategoryName(findName(data));
    }).catch(() => {});
  }, [slug]);

  useEffect(() => {
    setLoading(true);
    const params = { category: slug, sort, per_page: 12, page };
    api.get('/storefront/products', { params }).then((res) => {
      setProducts(res.data || []);
      setTotal(res.meta?.total || 0);
      setLastPage(res.meta?.last_page || 1);
    }).catch(() => {
      setProducts([]);
    }).finally(() => {
      setLoading(false);
    });
  }, [slug, sort, page]);

  return (
    <div className="pt-20">
      <div className="container mx-auto px-4 py-8">
        <nav className="flex items-center gap-2 text-sm text-gray-400 mb-6">
          <Link to="/" className="hover:text-gray-900 transition">Home</Link>
          <span className="text-gray-300">/</span>
          <span className="text-gray-900 capitalize font-medium">{categoryName}</span>
        </nav>

        <div className="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
          <h1 className="text-2xl font-bold">{categoryName}</h1>
          <div className="flex items-center gap-3">
            <span className="text-sm text-gray-400">{total} products</span>
            <select
              value={sort}
              onChange={(e) => { setSort(e.target.value); setPage(1); }}
              className="border border-gray-200 px-4 py-2 text-sm focus:outline-none focus:border-gray-900"
            >
              <option value="newest">Newest</option>
              <option value="price_asc">Price: Low to High</option>
              <option value="price_desc">Price: High to Low</option>
            </select>
          </div>
        </div>

        {loading ? (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            {Array.from({ length: 8 }).map((_, i) => (
              <div key={i} className="bg-gray-100 animate-pulse rounded-lg aspect-[3/4]" />
            ))}
          </div>
        ) : products.length === 0 ? (
          <div className="text-center py-12">
            <p className="text-gray-400 text-lg">No products in this category.</p>
            <Link to="/products" className="text-sm text-gray-900 underline mt-2 inline-block hover:no-underline">
              Browse All Products →
            </Link>
          </div>
        ) : (
          <>
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
              {products.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>

            {lastPage > 1 && (
              <div className="flex items-center justify-center gap-2 mt-10">
                {Array.from({ length: lastPage }).map((_, i) => (
                  <button
                    key={i}
                    onClick={() => setPage(i + 1)}
                    className={`w-8 h-8 text-xs font-medium transition ${
                      page === i + 1
                        ? 'bg-gray-900 text-white'
                        : 'border border-gray-200 text-gray-600 hover:border-gray-900'
                    }`}
                  >
                    {i + 1}
                  </button>
                ))}
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}
