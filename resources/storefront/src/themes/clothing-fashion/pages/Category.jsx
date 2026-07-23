import React, { useState, useEffect } from 'react';
import { useParams, Link, useSearchParams } from 'react-router-dom';
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

export default function Category() {
  const { slug } = useParams();
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
  const [attributes, setAttributes] = useState([]);
  const [priceRange, setPriceRange] = useState({ min: 0, max: 100000 });
  const [categoryName, setCategoryName] = useState(slug);

  const sort = searchParams.get('sort') || 'newest';
  const filterKey = searchParams.toString();

  useEffect(() => {
    api.get('/storefront/categories').then((data) => {
      if (!Array.isArray(data)) return;
      setCategories(data);
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
    api.get('/storefront/brands').then((data) => {
      if (Array.isArray(data)) setBrands(data);
    }).catch(() => {});
    api.get('/storefront/price-range').then((data) => {
      if (data?.max) setPriceRange({ min: data.min || 0, max: data.max });
    }).catch(() => {});
    api.get('/storefront/attributes').then((data) => {
      if (Array.isArray(data)) setAttributes(data);
    }).catch(() => {});
  }, [slug]);

  useEffect(() => {
    setLoading(true);
    const params = { category: slug, sort, per_page: 12, page };
    const search = searchParams.get('search') || '';
    if (search) params.search = search;
    searchParams.forEach((val, key) => {
      if (['category', 'brand', 'search', 'sort', 'min_price', 'max_price', 'per_page', 'page'].includes(key)) return;
      if (!params[key]) params[key] = val;
      else params[key] += ',' + val;
    });
    if (searchParams.get('min_price')) params.min_price = searchParams.get('min_price');
    if (searchParams.get('max_price')) params.max_price = searchParams.get('max_price');

    api.get('/storefront/products', { params }).then((res) => {
      setProducts(res.data || []);
      setTotal(res.meta?.total || 0);
      setLastPage(res.meta?.last_page || 1);
    }).catch(() => {
      setProducts([]);
    }).finally(() => {
      setLoading(false);
    });
  }, [slug, filterKey, sort, page]);

  const updateFilter = (key, values) => {
    const newParams = new URLSearchParams(searchParams);
    newParams.delete(key);
    values.forEach((v) => newParams.append(key, v));
    setSearchParams(newParams);
    setPage(1);
  };

  const toggleFilter = (key, value) => {
    const current = searchParams.getAll(key);
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
    setSearchParams({ sort });
    setPage(1);
  };

  const minPrice = searchParams.get('min_price') || '';
  const maxPrice = searchParams.get('max_price') || '';
  const hasFilters = ['brand', ...attributes.map((a) => a.slug)].some((k) => searchParams.getAll(k).length > 0) || !!minPrice || !!maxPrice;

  const toggleSection = (key) => setOpenSections((prev) => ({ ...prev, [key]: !prev[key] }));

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
        <div className="space-y-1 max-h-40 overflow-y-auto">
          {categories.map((cat) => (
            <div key={cat.slug}>
              <Link to={cat.slug === slug ? '#' : `/category/${cat.slug}`} className={`block py-1 text-sm transition ${cat.slug === slug ? 'text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-900'}`}>
                {cat.name}
              </Link>
              {cat.children?.length > 0 && (
                <div className="ml-4 space-y-0.5 border-l border-gray-100 pl-3">
                  {cat.children.map((child) => (
                    <Link key={child.slug} to={child.slug === slug ? '#' : `/category/${child.slug}`} className={`block py-0.5 text-sm transition ${child.slug === slug ? 'text-gray-900 font-medium' : 'text-gray-400 hover:text-gray-900'}`}>
                      {child.name}
                    </Link>
                  ))}
                </div>
              )}
            </div>
          ))}
        </div>
      </FilterSection>

      <FilterSection title="Price Range" open={openSections.price} onToggle={() => toggleSection('price')}>
        <div className="pt-2 px-1">
          <div className="relative h-2 mt-4 mb-6">
            <div className="absolute inset-0 bg-gray-200 rounded-full" />
            <div className="absolute h-full bg-gray-900 rounded-full" style={{ left: `${(Number(minPrice) || 0) / (priceRange.max / 100)}%`, right: `${100 - (Number(maxPrice) || priceRange.max) / (priceRange.max / 100)}%` }} />
            <input type="range" min={0} max={priceRange.max} step={100} value={Number(minPrice) || 0}
              onChange={(e) => { const val = +e.target.value; const max = Number(maxPrice) || priceRange.max; if (val <= max) handlePriceChange('min', val || ''); }}
              className="absolute inset-0 w-full appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-gray-900 [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-white [&::-webkit-slider-thumb]:shadow [&::-webkit-slider-thumb]:cursor-pointer [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:appearance-none [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-gray-900 [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-white [&::-moz-range-thumb]:shadow [&::-moz-range-thumb]:cursor-pointer" />
            <input type="range" min={0} max={priceRange.max} step={100} value={Number(maxPrice) || priceRange.max}
              onChange={(e) => { const val = +e.target.value; const min = Number(minPrice) || 0; if (val >= min) handlePriceChange('max', val >= priceRange.max ? '' : val); }}
              className="absolute inset-0 w-full appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-gray-900 [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-white [&::-webkit-slider-thumb]:shadow [&::-webkit-slider-thumb]:cursor-pointer [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:appearance-none [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-gray-900 [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-white [&::-moz-range-thumb]:shadow [&::-moz-range-thumb]:cursor-pointer" />
          </div>
          <div className="flex items-center justify-between text-xs text-gray-500">
            <span>{formatPrice(minPrice || 0)}</span>
            <span>{formatPrice(maxPrice || priceRange.max)}+</span>
          </div>
        </div>
      </FilterSection>

      <FilterSection title="Brand" open={openSections.brand} onToggle={() => toggleSection('brand')}>
        <div className="space-y-1.5 max-h-40 overflow-y-auto">
          {brands.map((b) => (
            <label key={b.slug} className="flex items-center gap-2.5 py-1 cursor-pointer group">
              <input type="checkbox" checked={searchParams.getAll('brand').includes(b.slug)}
                onChange={() => toggleFilter('brand', b.slug)} className="accent-gray-900 w-3.5 h-3.5" />
              <span className="text-sm text-gray-600 group-hover:text-gray-900 transition">{b.name}</span>
            </label>
          ))}
        </div>
      </FilterSection>

      {attributes.map((attr) => (
        <FilterSection key={attr.slug} title={attr.name} open={openSections[attr.slug] ?? true} onToggle={() => toggleSection(attr.slug)}>
          <div className="space-y-1 max-h-40 overflow-y-auto">
            {attr.values.map((val) => (
              <label key={val} className="flex items-center gap-2.5 py-0.5 cursor-pointer group">
                <input type="checkbox" checked={searchParams.getAll(attr.slug).includes(val)}
                  onChange={() => toggleFilter(attr.slug, val)} className="accent-gray-900 w-3.5 h-3.5" />
                <span className="text-sm text-gray-500 group-hover:text-gray-900 transition">{val}</span>
              </label>
            ))}
          </div>
        </FilterSection>
      ))}
    </div>
  );

  return (
    <div className="pt-20">
      <div className="container mx-auto px-4 py-8">
        <nav className="flex items-center gap-2 text-sm text-gray-400 mb-6">
          <Link to="/" className="hover:text-gray-900 transition">Home</Link>
          <span className="text-gray-300">/</span>
          <span className="text-gray-900 capitalize font-medium">{categoryName}</span>
        </nav>

        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center gap-3">
            <div className="w-1 h-6 bg-gray-900" />
            <h1 className="text-lg md:text-2xl font-bold tracking-tight">{categoryName}</h1>
            <span className="text-sm text-gray-400">({total})</span>
          </div>
          <div className="flex items-center gap-3">
            <button onClick={() => setMobileFilter(true)} className="lg:hidden flex items-center gap-1.5 text-xs uppercase tracking-wider border border-gray-200 px-3 py-2 hover:border-gray-900 transition">
              <SlidersHorizontal className="w-3.5 h-3.5" />
              Filter
            </button>
            <div className="relative">
              <select value={sort}
                onChange={(e) => { const p = new URLSearchParams(searchParams); p.set('sort', e.target.value); setSearchParams(p); setPage(1); }}
                className="appearance-none bg-white border border-gray-200 rounded-lg pl-3 pr-8 py-2.5 text-xs font-medium text-gray-700 focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition cursor-pointer hover:border-gray-400"
              >
                <option value="newest">Newest</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
              </select>
              <svg className="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" /></svg>
            </div>
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
                  <div key={i} className="bg-white rounded-xl border border-gray-100 overflow-hidden animate-pulse">
                    <div className="aspect-[3/4] bg-gray-200" />
                    <div className="p-4 space-y-3">
                      <div className="h-2.5 w-16 bg-gray-200 rounded" />
                      <div className="h-3.5 w-full bg-gray-200 rounded" />
                      <div className="h-5 w-20 bg-gray-200 rounded" />
                      <div className="flex gap-1.5">{Array.from({ length: 4 }).map((_, j) => (<div key={j} className="w-5 h-5 rounded-full bg-gray-200" />))}</div>
                      <div className="flex gap-1.5">{Array.from({ length: 3 }).map((_, j) => (<div key={j} className="h-6 w-10 bg-gray-200 rounded-md" />))}</div>
                    </div>
                  </div>
                ))}
              </div>
            ) : products.length === 0 ? (
              <div className="text-center py-16">
                <p className="text-gray-400 text-lg">No products match your filters.</p>
                {hasFilters && (
                  <button onClick={clearFilters} className="text-sm text-gray-900 underline mt-2 hover:no-underline">Clear filters</button>
                )}
                {!hasFilters && (
                  <Link to="/products" className="text-sm text-gray-900 underline mt-2 inline-block hover:no-underline">Browse All Products →</Link>
                )}
              </div>
            ) : (
              <>
                <div className="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-5">
                  {products.map((product, i) => (
                    <div key={product.id} className="animate-fade-in" style={{ animationDelay: `${i * 40}ms` }}>
                      <ProductCard product={product} />
                    </div>
                  ))}
                </div>
                {lastPage > 1 && (
                  <div className="flex items-center justify-center gap-2 mt-10">
                    {Array.from({ length: lastPage }).map((_, i) => (
                      <button key={i} onClick={() => setPage(i + 1)}
                        className={`w-8 h-8 text-xs font-medium transition ${page === i + 1 ? 'bg-gray-900 text-white' : 'border border-gray-200 text-gray-600 hover:border-gray-900'}`}>{i + 1}</button>
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
