import React, { useState, useMemo } from 'react';
import { SlidersHorizontal, X, ChevronDown } from 'lucide-react';
import ProductCard from '../components/ProductCard';

const allProducts = [
  { id: 1, name: 'Classic Cotton T-Shirt', slug: 'classic-cotton-tshirt', base_price: 1200, discount_price: 799, effective_price: 799, stock_quantity: 50, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&q=80', category: 'T-Shirts', brand: 'Nike', color: 'Black', size: 'M' },
  { id: 2, name: 'Slim Fit Denim Jeans', slug: 'slim-fit-denim-jeans', base_price: 2500, discount_price: 1899, effective_price: 1899, stock_quantity: 35, image: 'https://images.unsplash.com/photo-1542272454315-4c01d7abdf4a?w=600&q=80', category: 'Denim', brand: 'Levis', color: 'Blue', size: '32' },
  { id: 3, name: 'Oversized Hoodie', slug: 'oversized-hoodie', base_price: 1800, discount_price: null, effective_price: 1800, stock_quantity: 20, image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&q=80', category: 'Hoodies', brand: 'Adidas', color: 'Gray', size: 'L' },
  { id: 4, name: 'Leather Biker Jacket', slug: 'leather-biker-jacket', base_price: 5500, discount_price: 4200, effective_price: 4200, stock_quantity: 10, image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&q=80', category: 'Jackets', brand: 'Zara', color: 'Black', size: 'L' },
  { id: 5, name: 'Running Shoes Pro', slug: 'running-shoes-pro', base_price: 3200, discount_price: 2599, effective_price: 2599, stock_quantity: 45, image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&q=80', category: 'Shoes', brand: 'Nike', color: 'White', size: '42' },
  { id: 6, name: 'Premium Cap', slug: 'premium-cap', base_price: 600, discount_price: 399, effective_price: 399, stock_quantity: 100, image: 'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?w=600&q=80', category: 'Accessories', brand: 'Adidas', color: 'Black', size: 'Free' },
  { id: 7, name: 'Graphic Print T-Shirt', slug: 'graphic-print-tshirt', base_price: 1500, discount_price: null, effective_price: 1500, stock_quantity: 40, image: 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=600&q=80', category: 'T-Shirts', brand: 'Zara', color: 'White', size: 'L' },
  { id: 8, name: 'Cargo Pants', slug: 'cargo-pants', base_price: 2200, discount_price: 1799, effective_price: 1799, stock_quantity: 25, image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600&q=80', category: 'Denim', brand: 'Levis', color: 'Green', size: '32' },
  { id: 9, name: 'Wool Blend Hoodie', slug: 'wool-blend-hoodie', base_price: 2400, discount_price: 1999, effective_price: 1999, stock_quantity: 15, image: 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=600&q=80', category: 'Hoodies', brand: 'Nike', color: 'Navy', size: 'XL' },
  { id: 10, name: 'Bomber Jacket', slug: 'bomber-jacket', base_price: 4800, discount_price: 3800, effective_price: 3800, stock_quantity: 8, image: 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?w=600&q=80', category: 'Jackets', brand: 'Zara', color: 'Green', size: 'M' },
  { id: 11, name: 'Summer Linen Shirt', slug: 'summer-linen-shirt', base_price: 1600, discount_price: null, effective_price: 1600, stock_quantity: 30, image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600&q=80', category: 'T-Shirts', brand: 'H&M', color: 'White', size: 'S' },
  { id: 12, name: 'Casual Sneakers', slug: 'casual-sneakers', base_price: 2800, discount_price: null, effective_price: 2800, stock_quantity: 40, image: 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600&q=80', category: 'Shoes', brand: 'Puma', color: 'White', size: '42' },
];

const categories = ['T-Shirts', 'Denim', 'Hoodies', 'Jackets', 'Shoes', 'Accessories'];
const brands = ['Nike', 'Adidas', 'Levis', 'Zara', 'H&M', 'Puma'];
const colors = ['Black', 'White', 'Blue', 'Gray', 'Green', 'Navy'];
const sizes = ['S', 'M', 'L', 'XL', 'XXL', '32', '42', 'Free'];

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
    let result = [...allProducts];
    if (filters.category.length > 0) result = result.filter((p) => filters.category.includes(p.category));
    if (filters.brand.length > 0) result = result.filter((p) => filters.brand.includes(p.brand));
    if (filters.color.length > 0) result = result.filter((p) => filters.color.includes(p.color));
    if (filters.size.length > 0) result = result.filter((p) => filters.size.includes(p.size));
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
        <div className="flex items-center gap-2">
          <input
            type="number"
            placeholder="Min"
            value={filters.minPrice}
            onChange={(e) => handlePriceChange('minPrice', e.target.value)}
            className="w-full border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-gray-900"
          />
          <span className="text-gray-300">-</span>
          <input
            type="number"
            placeholder="Max"
            value={filters.maxPrice}
            onChange={(e) => handlePriceChange('maxPrice', e.target.value)}
            className="w-full border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-gray-900"
          />
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
