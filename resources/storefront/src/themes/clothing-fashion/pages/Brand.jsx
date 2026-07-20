import React, { useState, useMemo } from 'react';
import { useParams, Link } from 'react-router-dom';
import ProductCard from '../components/ProductCard';

const allProducts = [
  { id: 1, name: 'Classic Cotton T-Shirt', slug: 'classic-cotton-tshirt', base_price: 1200, discount_price: 799, effective_price: 799, stock_quantity: 50, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&q=80', category: { name: 'T-Shirts', slug: 't-shirts' }, brand: { name: 'Nike', slug: 'nike' }, color: 'Black', size: 'M' },
  { id: 2, name: 'Slim Fit Denim Jeans', slug: 'slim-fit-denim-jeans', base_price: 2500, discount_price: 1899, effective_price: 1899, stock_quantity: 35, image: 'https://images.unsplash.com/photo-1542272454315-4c01d7abdf4a?w=600&q=80', category: { name: 'Denim', slug: 'denim' }, brand: { name: 'Levis', slug: 'levis' }, color: 'Blue', size: '32' },
  { id: 3, name: 'Oversized Hoodie', slug: 'oversized-hoodie', base_price: 1800, discount_price: null, effective_price: 1800, stock_quantity: 20, image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&q=80', category: { name: 'Hoodies', slug: 'hoodies' }, brand: { name: 'Adidas', slug: 'adidas' }, color: 'Gray', size: 'L' },
  { id: 4, name: 'Leather Biker Jacket', slug: 'leather-biker-jacket', base_price: 5500, discount_price: 4200, effective_price: 4200, stock_quantity: 10, image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&q=80', category: { name: 'Jackets', slug: 'jackets' }, brand: { name: 'Zara', slug: 'zara' }, color: 'Black', size: 'L' },
  { id: 5, name: 'Running Shoes Pro', slug: 'running-shoes-pro', base_price: 3200, discount_price: 2599, effective_price: 2599, stock_quantity: 45, image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&q=80', category: { name: 'Shoes', slug: 'shoes' }, brand: { name: 'Nike', slug: 'nike' }, color: 'White', size: '42' },
  { id: 6, name: 'Premium Cap', slug: 'premium-cap', base_price: 600, discount_price: 399, effective_price: 399, stock_quantity: 100, image: 'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?w=600&q=80', category: { name: 'Accessories', slug: 'accessories' }, brand: { name: 'Adidas', slug: 'adidas' }, color: 'Black', size: 'Free' },
  { id: 7, name: 'Graphic Print T-Shirt', slug: 'graphic-print-tshirt', base_price: 1500, discount_price: null, effective_price: 1500, stock_quantity: 40, image: 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=600&q=80', category: { name: 'T-Shirts', slug: 't-shirts' }, brand: { name: 'Zara', slug: 'zara' }, color: 'White', size: 'L' },
  { id: 8, name: 'Cargo Pants', slug: 'cargo-pants', base_price: 2200, discount_price: 1799, effective_price: 1799, stock_quantity: 25, image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600&q=80', category: { name: 'Denim', slug: 'denim' }, brand: { name: 'Levis', slug: 'levis' }, color: 'Green', size: '32' },
  { id: 9, name: 'Wool Blend Hoodie', slug: 'wool-blend-hoodie', base_price: 2400, discount_price: 1999, effective_price: 1999, stock_quantity: 15, image: 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=600&q=80', category: { name: 'Hoodies', slug: 'hoodies' }, brand: { name: 'Nike', slug: 'nike' }, color: 'Navy', size: 'XL' },
  { id: 10, name: 'Bomber Jacket', slug: 'bomber-jacket', base_price: 4800, discount_price: 3800, effective_price: 3800, stock_quantity: 8, image: 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?w=600&q=80', category: { name: 'Jackets', slug: 'jackets' }, brand: { name: 'Zara', slug: 'zara' }, color: 'Green', size: 'M' },
  { id: 11, name: 'Summer Linen Shirt', slug: 'summer-linen-shirt', base_price: 1600, discount_price: null, effective_price: 1600, stock_quantity: 30, image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600&q=80', category: { name: 'T-Shirts', slug: 't-shirts' }, brand: { name: 'H&M', slug: 'hm' }, color: 'White', size: 'S' },
  { id: 12, name: 'Casual Sneakers', slug: 'casual-sneakers', base_price: 2800, discount_price: null, effective_price: 2800, stock_quantity: 40, image: 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600&q=80', category: { name: 'Shoes', slug: 'shoes' }, brand: { name: 'Puma', slug: 'puma' }, color: 'White', size: '42' },
];

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
