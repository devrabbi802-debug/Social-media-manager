import React, { useState, useEffect, useMemo } from 'react';
import { useSearchParams } from 'react-router-dom';
import { SlidersHorizontal, X, ChevronDown } from 'lucide-react';
import ProductCard from '../components/ProductCard';
import api from '../../../api/client';

function FilterSection({ title, open, onToggle, children }) {
  return (
    <div className="border-b border-gray-100 py-4">
      <button onClick={onToggle} className="flex items-center justify-between w-full text-left">
        <span className="text-xs font-bold uppercase tracking-wider">{title}</span>
        <ChevronDown className={`w-3.5 h-3.5 transition-transform ${open ? 'rotate-180' : ''}`} />
      </button>
      {open && <div className="mt-3 space-y-2">{children}</div>}
    </div>
  );
}

export default function Products() {
  const [searchParams, setSearchParams] = useSearchParams();
  const [mobileFilter, setMobileFilter] = useState(false);
  const [openSections, setOpenSections] = useState({ category: true, price: true, brand: true });

  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [total, setTotal] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const [page, setPage] = useState(1);

  const [categories, setCategories] = useState([]);
  const [brands, setBrands] = useState([]);

  const searchValue = searchParams.get('search') || '';
  const minPrice = searchParams.get('min_price') || '';
  const maxPrice = searchParams.get('max_price') || '';
  const sort = searchParams.get('sort') || 'newest';
  const filterKey = searchParams.toString();

  useEffect(() => {
    api.get('/storefront/categories').then((data) => {
      if (Array.isArray(data)) setCategories(data);
    }).catch(() => {});
    api.get('/storefront/brands').then((data) => {
      if (Array.isArray(data)) setBrands(data);
    }).catch(() => {});
  }, []);

  useEffect(() => {
    setLoading(true);
    const params = { per_page: 12, page, sort };
    const selectedCategories = searchParams.getAll('category');
    const selectedBrands = searchParams.getAll('brand');
    const search = searchParams.get('search') || '';
    if (search) params.search = search;
    if (selectedCategories.length > 0) params.category = selectedCategories.join(',');
    if (selectedBrands.length > 0) params.brand = selectedBrands.join(',');
    if (minPrice) params.min_price = minPrice;
    if (maxPrice) params.max_price = maxPrice;

    api.get('/storefront/products', { params }).then((res) => {
      setProducts(res.data || []);
      setTotal(res.meta?.total || 0);
      setLastPage(res.meta?.last_page || 1);
    }).catch(() => {
      setProducts([]);
    }).finally(() => {
      setLoading(false);
    });
  }, [filterKey, sort, page]);

  const updateFilter = (key, values) => {
    const newParams = new URLSearchParams(searchParams);
    newParams.delete(key);
    values.forEach((v) => newParams.append(key, v));
    setSearchParams(newParams);
    setPage(1);
  };

  const toggleFilter = (key, value) => {
    const current = key === 'category' ? searchParams.getAll('category') : searchParams.getAll('brand');
    const updated = current.includes(value)
      ? current.filter((v) => v !== value)
      : [...current, value];
    updateFilter(key, updated);
  };

  const handlePriceChange = (key, value) => {
    const newParams = new URLSearchParams(searchParams);
    if (value) newParams.set(key === 'min' ? 'min_price' : 'max_price', value);
    else newParams.delete(key === 'min' ? 'min_price' : 'max_price');
    setSearchParams(newParams);
    setPage(1);
  };

  const clearFilters = () => {
    setSearchParams({});
    setPage(1);
  };

  const hasFilters = searchParams.getAll('category').length > 0 || searchParams.getAll('brand').length > 0 || minPrice || maxPrice;

  const toggleSection = (key) => setOpenSections((prev) => ({ ...prev, [key]: !prev[key] }));

  const allCategoryOptions = useMemo(() => {
    const flat = [];
    categories.forEach((cat) => {
      flat.push({ slug: cat.slug, name: cat.name });
      (cat.children || []).forEach((child) => {
        flat.push({ slug: child.slug, name: child.name });
      });
    });
    return flat;
  }, [categories]);

  const maxPriceLimit = 100000;

  const renderFilterCheckboxes = (key, options) => {
    const selected = key === 'category' ? searchParams.getAll('category') : searchParams.getAll('brand');
    return (
      <div className="space-y-1.5 max-h-40 overflow-y-auto">
        {options.map((opt) => (
          <label key={opt.slug} className="flex items-center gap-2.5 py-1 cursor-pointer group">
            <input
              type="checkbox"
              checked={selected.includes(opt.slug)}
              onChange={() => toggleFilter(key, opt.slug)}
              className="accent-gray-900 w-3.5 h-3.5"
            />
            <span className="text-sm text-gray-600 group-hover:text-gray-900 transition">{opt.name}</span>
          </label>
        ))}
      </div>
    );
  };

  const formatPrice = (val) => `৳${Number(val).toLocaleString()}`;

  const filterContent = (
    <div>
      <div className="flex items-center justify-between mb-2">
        <span className="text-sm font-bold uppercase tracking-wider">Filters</span>
        {hasFilters && (
          <button onClick={clearFilters} className="text-xs text-gray-400 hover:text-gray-900 transition underline">
            Clear All
          </button>
        )}
      </div>

      <FilterSection title="Category" open={openSections.category} onToggle={() => toggleSection('category')}>
        {renderFilterCheckboxes('category', allCategoryOptions)}
      </FilterSection>

      <FilterSection title="Price Range" open={openSections.price} onToggle={() => toggleSection('price')}>
        <div className="pt-2 px-1">
          <div className="relative h-2 mt-4 mb-6">
            <div className="absolute inset-0 bg-gray-200 rounded-full" />
            <div
              className="absolute h-full bg-gray-900 rounded-full"
              style={{ left: `${(Number(minPrice) || 0) / (maxPriceLimit / 100)}%`, right: `${100 - (Number(maxPrice) || maxPriceLimit) / (maxPriceLimit / 100)}%` }}
            />
            <input
              type="range"
              min={0}
              max={maxPriceLimit}
              step={100}
              value={Number(minPrice) || 0}
              onChange={(e) => {
                const val = Number(e.target.value);
                const max = Number(maxPrice) || maxPriceLimit;
                if (val <= max) handlePriceChange('min', val || '');
              }}
              className="absolute inset-0 w-full appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-gray-900 [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-white [&::-webkit-slider-thumb]:shadow [&::-webkit-slider-thumb]:cursor-pointer [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:appearance-none [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-gray-900 [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-white [&::-moz-range-thumb]:shadow [&::-moz-range-thumb]:cursor-pointer"
            />
            <input
              type="range"
              min={0}
              max={maxPriceLimit}
              step={100}
              value={Number(maxPrice) || maxPriceLimit}
              onChange={(e) => {
                const val = Number(e.target.value);
                const min = Number(minPrice) || 0;
                if (val >= min) handlePriceChange('max', val >= maxPriceLimit ? '' : val);
              }}
              className="absolute inset-0 w-full appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-gray-900 [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-white [&::-webkit-slider-thumb]:shadow [&::-webkit-slider-thumb]:cursor-pointer [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:appearance-none [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-gray-900 [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-white [&::-moz-range-thumb]:shadow [&::-moz-range-thumb]:cursor-pointer"
            />
          </div>
          <div className="flex items-center justify-between text-xs text-gray-500">
            <span>{formatPrice(minPrice || 0)}</span>
            <span>{formatPrice(maxPrice || maxPriceLimit)}+</span>
          </div>
        </div>
      </FilterSection>

      <FilterSection title="Brand" open={openSections.brand} onToggle={() => toggleSection('brand')}>
        {renderFilterCheckboxes('brand', brands.map((b) => ({ slug: b.slug, name: b.name })))}
      </FilterSection>
    </div>
  );

  return (
    <div className="pt-20">
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center gap-3">
            <div className="w-1 h-6 bg-gray-900" />
            <h1 className="text-lg md:text-2xl font-bold tracking-tight">{searchValue ? `Search: "${searchValue}"` : 'All Products'}</h1>
            <span className="text-sm text-gray-400">({total})</span>
          </div>
          <div className="flex items-center gap-3">
            <button onClick={() => setMobileFilter(true)} className="lg:hidden flex items-center gap-1.5 text-xs uppercase tracking-wider border border-gray-200 px-3 py-2 hover:border-gray-900 transition">
              <SlidersHorizontal className="w-3.5 h-3.5" />
              Filter
            </button>
            <select
              value={sort}
              onChange={(e) => {
                const newParams = new URLSearchParams(searchParams);
                newParams.set('sort', e.target.value);
                setSearchParams(newParams);
                setPage(1);
              }}
              className="border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:border-gray-900 uppercase tracking-wider"
            >
              <option value="newest">Newest</option>
              <option value="price_asc">Price: Low</option>
              <option value="price_desc">Price: High</option>
            </select>
          </div>
        </div>

        <div className="flex gap-8">
          <aside className="hidden lg:block w-56 flex-shrink-0">
            <div className="sticky top-24">{filterContent}</div>
          </aside>

          {mobileFilter && (
            <>
              <div className="fixed inset-0 bg-black/40 z-50 lg:hidden" onClick={() => setMobileFilter(false)} />
              <div className="fixed inset-y-0 left-0 w-72 bg-white z-50 p-6 overflow-y-auto lg:hidden">
                <div className="flex items-center justify-between mb-6">
                  <span className="text-sm font-bold uppercase tracking-wider">Filters</span>
                  <button onClick={() => setMobileFilter(false)}><X className="w-5 h-5" /></button>
                </div>
                {filterContent}
              </div>
            </>
          )}

          <div className="flex-1 min-w-0">
            {loading ? (
              <div className="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-5">
                {Array.from({ length: 8 }).map((_, i) => (
                  <div key={i} className="bg-gray-100 animate-pulse rounded-lg aspect-[3/4]" />
                ))}
              </div>
            ) : products.length === 0 ? (
              <div className="text-center py-16">
                <p className="text-gray-400 text-lg">No products match your filters.</p>
                {hasFilters && (
                  <button onClick={clearFilters} className="text-sm text-gray-900 underline mt-2 hover:no-underline">
                    Clear filters
                  </button>
                )}
              </div>
            ) : (
              <>
                <div className="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-5">
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
      </div>
    </div>
  );
}
