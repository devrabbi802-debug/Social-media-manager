import React, { useState, useMemo } from 'react';
import { SlidersHorizontal, X, ChevronDown } from 'lucide-react';
import ProductCard from '../components/ProductCard';
import { allProducts as sharedProducts } from '../data/products';

const categories = [...new Set(sharedProducts.map((p) => p.category.name))];
const brands = [...new Set(sharedProducts.map((p) => p.brand.name))];
const colors = [...new Set(sharedProducts.flatMap((p) => p.variants?.map((v) => v.color) || []))];
const sizes = [...new Set(sharedProducts.flatMap((p) => p.variants?.map((v) => v.size) || []))];

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
  const [mobileFilter, setMobileFilter] = useState(false);
  const [openSections, setOpenSections] = useState({ category: true, price: true, color: true, size: true, brand: true });

  const [filters, setFilters] = useState({
    category: [],
    brand: [],
    color: [],
    size: [],
    minPrice: '',
    maxPrice: '',
  });
  const [sort, setSort] = useState('newest');
  const [visibleCount, setVisibleCount] = useState(8);

  const toggleSection = (key) => setOpenSections((prev) => ({ ...prev, [key]: !prev[key] }));

  const toggleFilter = (key, value) => {
    setFilters((prev) => ({
      ...prev,
      [key]: prev[key].includes(value) ? prev[key].filter((v) => v !== value) : [...prev[key], value],
    }));
    setVisibleCount(8);
  };

  const handlePriceChange = (key, value) => {
    setFilters((prev) => ({ ...prev, [key]: value }));
    setVisibleCount(8);
  };

  const filteredProducts = useMemo(() => {
    let result = [...sharedProducts];
    if (filters.category.length > 0) result = result.filter((p) => filters.category.includes(p.category.name));
    if (filters.brand.length > 0) result = result.filter((p) => filters.brand.includes(p.brand.name));
    if (filters.color.length > 0) result = result.filter((p) => p.variants?.some((v) => filters.color.includes(v.color)));
    if (filters.size.length > 0) result = result.filter((p) => p.variants?.some((v) => filters.size.includes(v.size)));
    if (filters.minPrice) result = result.filter((p) => p.effective_price >= Number(filters.minPrice));
    if (filters.maxPrice) result = result.filter((p) => p.effective_price <= Number(filters.maxPrice));
    if (sort === 'price_asc') result.sort((a, b) => a.effective_price - b.effective_price);
    if (sort === 'price_desc') result.sort((a, b) => b.effective_price - a.effective_price);
    return result;
  }, [filters, sort]);

  const showMore = () => setVisibleCount((prev) => prev + 8);
  const displayedProducts = filteredProducts.slice(0, visibleCount);
  const hasMore = visibleCount < filteredProducts.length;

  const clearFilters = () => {
    setFilters({ category: [], brand: [], color: [], size: [], minPrice: '', maxPrice: '' });
    setVisibleCount(8);
  };

  const hasFilters = Object.values(filters).some((v) => (Array.isArray(v) ? v.length > 0 : v !== ''));

  const renderFilterCheckboxes = (key, options) => (
    <div className="space-y-1.5 max-h-40 overflow-y-auto">
      {options.map((option) => (
        <label key={option} className="flex items-center gap-2.5 py-1 cursor-pointer group">
          <input
            type="checkbox"
            checked={filters[key].includes(option)}
            onChange={() => toggleFilter(key, option)}
            className="accent-gray-900 w-3.5 h-3.5"
          />
          <span className="text-sm text-gray-600 group-hover:text-gray-900 transition">{option}</span>
        </label>
      ))}
    </div>
  );

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
        {renderFilterCheckboxes('category', categories)}
      </FilterSection>

      <FilterSection title="Price Range" open={openSections.price} onToggle={() => toggleSection('price')}>
        <div className="pt-2 px-1">
          <div className="relative h-2 mt-4 mb-6">
            <div className="absolute inset-0 bg-gray-200 rounded-full" />
            <div
              className="absolute h-full bg-gray-900 rounded-full"
              style={{ left: `${(Number(filters.minPrice) || 0) / 100}%`, right: `${100 - (Number(filters.maxPrice) || 10000) / 100}%` }}
            />
            <input
              type="range"
              min={0}
              max={10000}
              step={100}
              value={filters.minPrice || 0}
              onChange={(e) => {
                const val = Number(e.target.value);
                const max = Number(filters.maxPrice) || 10000;
                if (val <= max) handlePriceChange('minPrice', val);
              }}
              className="absolute inset-0 w-full appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-gray-900 [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-white [&::-webkit-slider-thumb]:shadow [&::-webkit-slider-thumb]:cursor-pointer [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:appearance-none [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-gray-900 [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-white [&::-moz-range-thumb]:shadow [&::-moz-range-thumb]:cursor-pointer"
            />
            <input
              type="range"
              min={0}
              max={10000}
              step={100}
              value={filters.maxPrice || 10000}
              onChange={(e) => {
                const val = Number(e.target.value);
                const min = Number(filters.minPrice) || 0;
                if (val >= min) handlePriceChange('maxPrice', val);
              }}
              className="absolute inset-0 w-full appearance-none bg-transparent pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-gray-900 [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-white [&::-webkit-slider-thumb]:shadow [&::-webkit-slider-thumb]:cursor-pointer [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:appearance-none [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-gray-900 [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-white [&::-moz-range-thumb]:shadow [&::-moz-range-thumb]:cursor-pointer"
            />
          </div>
          <div className="flex items-center justify-between text-xs text-gray-500">
            <span>৳{Number(filters.minPrice) || 0}</span>
            <span>৳{Number(filters.maxPrice) || 10000}</span>
          </div>
        </div>
      </FilterSection>

      <FilterSection title="Color" open={openSections.color} onToggle={() => toggleSection('color')}>
        {renderFilterCheckboxes('color', colors)}
      </FilterSection>

      <FilterSection title="Size" open={openSections.size} onToggle={() => toggleSection('size')}>
        {renderFilterCheckboxes('size', sizes)}
      </FilterSection>

      <FilterSection title="Brand" open={openSections.brand} onToggle={() => toggleSection('brand')}>
        {renderFilterCheckboxes('brand', brands)}
      </FilterSection>
    </div>
  );

  return (
    <div className="pt-20">
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center gap-3">
            <div className="w-1 h-6 bg-gray-900" />
            <h1 className="text-lg md:text-2xl font-bold tracking-tight">All Products</h1>
            <span className="text-sm text-gray-400">({filteredProducts.length})</span>
          </div>
          <div className="flex items-center gap-3">
            <button onClick={() => setMobileFilter(true)} className="lg:hidden flex items-center gap-1.5 text-xs uppercase tracking-wider border border-gray-200 px-3 py-2 hover:border-gray-900 transition">
              <SlidersHorizontal className="w-3.5 h-3.5" />
              Filter
            </button>
            <select
              value={sort}
              onChange={(e) => setSort(e.target.value)}
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
            {displayedProducts.length === 0 ? (
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
                  {displayedProducts.map((product) => (
                    <ProductCard key={product.id} product={product} />
                  ))}
                </div>

                {hasMore && (
                  <div className="flex justify-center mt-10">
                    <button
                      onClick={showMore}
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
      </div>
    </div>
  );
}
