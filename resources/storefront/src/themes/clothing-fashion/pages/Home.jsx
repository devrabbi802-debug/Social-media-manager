import React, { useMemo } from 'react';
import HeroBanner from '../components/HeroBanner';
import CategoryGrid from '../components/CategoryGrid';
import ProductSection from '../components/ProductSection';
import CategoryBanner from '../components/CategoryBanner';
import CategoryProducts from '../components/CategoryProducts';
import Features from '../components/Features';

const staticData = {
  categories: [
    { id: 1, name: 'T-Shirts', slug: 't-shirts', image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&q=80', products_count: 24 },
    { id: 2, name: 'Denim', slug: 'denim', image: 'https://images.unsplash.com/photo-1542272454315-4c01d7abdf4a?w=800&q=80', products_count: 18 },
    { id: 3, name: 'Hoodies', slug: 'hoodies', image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=800&q=80', products_count: 15 },
    { id: 4, name: 'Jackets', slug: 'jackets', image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=800&q=80', products_count: 12 },
    { id: 5, name: 'Shoes', slug: 'shoes', image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&q=80', products_count: 30 },
    { id: 6, name: 'Accessories', slug: 'accessories', image: 'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?w=800&q=80', products_count: 20 },
  ],
  bestSelling: [
    { id: 1, name: 'Classic Cotton T-Shirt', slug: 'classic-cotton-tshirt', base_price: 1200, discount_price: 799, effective_price: 799, stock_quantity: 50, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&q=80', category: { name: 'T-Shirts', slug: 't-shirts' } },
    { id: 2, name: 'Slim Fit Denim Jeans', slug: 'slim-fit-denim-jeans', base_price: 2500, discount_price: 1899, effective_price: 1899, stock_quantity: 35, image: 'https://images.unsplash.com/photo-1542272454315-4c01d7abdf4a?w=600&q=80', category: { name: 'Denim', slug: 'denim' } },
    { id: 3, name: 'Oversized Hoodie', slug: 'oversized-hoodie', base_price: 1800, discount_price: null, effective_price: 1800, stock_quantity: 20, image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&q=80', category: { name: 'Hoodies', slug: 'hoodies' } },
    { id: 4, name: 'Leather Biker Jacket', slug: 'leather-biker-jacket', base_price: 5500, discount_price: 4200, effective_price: 4200, stock_quantity: 10, image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&q=80', category: { name: 'Jackets', slug: 'jackets' } },
    { id: 5, name: 'Running Shoes Pro', slug: 'running-shoes-pro', base_price: 3200, discount_price: 2599, effective_price: 2599, stock_quantity: 45, image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&q=80', category: { name: 'Shoes', slug: 'shoes' } },
    { id: 6, name: 'Premium Cap', slug: 'premium-cap', base_price: 600, discount_price: 399, effective_price: 399, stock_quantity: 100, image: 'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?w=600&q=80', category: { name: 'Accessories', slug: 'accessories' } },
    { id: 7, name: 'Graphic Print T-Shirt', slug: 'graphic-print-tshirt', base_price: 1500, discount_price: null, effective_price: 1500, stock_quantity: 40, image: 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=600&q=80', category: { name: 'T-Shirts', slug: 't-shirts' } },
    { id: 8, name: 'Cargo Pants', slug: 'cargo-pants', base_price: 2200, discount_price: 1799, effective_price: 1799, stock_quantity: 25, image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600&q=80', category: { name: 'Denim', slug: 'denim' } },
    { id: 9, name: 'Wool Blend Hoodie', slug: 'wool-blend-hoodie', base_price: 2400, discount_price: 1999, effective_price: 1999, stock_quantity: 15, image: 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=600&q=80', category: { name: 'Hoodies', slug: 'hoodies' } },
    { id: 10, name: 'Bomber Jacket', slug: 'bomber-jacket', base_price: 4800, discount_price: 3800, effective_price: 3800, stock_quantity: 8, image: 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?w=600&q=80', category: { name: 'Jackets', slug: 'jackets' } },
  ],
  newArrivals: [
    { id: 11, name: 'Summer Linen Shirt', slug: 'summer-linen-shirt', base_price: 1600, discount_price: null, effective_price: 1600, stock_quantity: 30, image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600&q=80', category: { name: 'T-Shirts', slug: 't-shirts' } },
    { id: 12, name: 'Ripped Skinny Jeans', slug: 'ripped-skinny-jeans', base_price: 2800, discount_price: 2200, effective_price: 2200, stock_quantity: 20, image: 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=600&q=80', category: { name: 'Denim', slug: 'denim' } },
    { id: 13, name: 'Zip Up Hoodie', slug: 'zip-up-hoodie', base_price: 2100, discount_price: 1699, effective_price: 1699, stock_quantity: 18, image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&q=80', category: { name: 'Hoodies', slug: 'hoodies' } },
    { id: 14, name: 'Denim Jacket', slug: 'denim-jacket', base_price: 4200, discount_price: 3500, effective_price: 3500, stock_quantity: 12, image: 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=600&q=80', category: { name: 'Jackets', slug: 'jackets' } },
    { id: 15, name: 'Casual Sneakers', slug: 'casual-sneakers', base_price: 2800, discount_price: null, effective_price: 2800, stock_quantity: 40, image: 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600&q=80', category: { name: 'Shoes', slug: 'shoes' } },
    { id: 16, name: 'Leather Belt', slug: 'leather-belt', base_price: 900, discount_price: 699, effective_price: 699, stock_quantity: 60, image: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&q=80', category: { name: 'Accessories', slug: 'accessories' } },
    { id: 17, name: 'Polo T-Shirt', slug: 'polo-tshirt', base_price: 1400, discount_price: 1099, effective_price: 1099, stock_quantity: 35, image: 'https://images.unsplash.com/photo-1598713125249-ba7e3b3c02ba?w=600&q=80', category: { name: 'T-Shirts', slug: 't-shirts' } },
    { id: 18, name: 'Chino Pants', slug: 'chino-pants', base_price: 2000, discount_price: null, effective_price: 2000, stock_quantity: 22, image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600&q=80', category: { name: 'Denim', slug: 'denim' } },
    { id: 19, name: 'Sport Shoes Elite', slug: 'sport-shoes-elite', base_price: 4500, discount_price: 3600, effective_price: 3600, stock_quantity: 15, image: 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=600&q=80', category: { name: 'Shoes', slug: 'shoes' } },
    { id: 20, name: 'Winter Beanie', slug: 'winter-beanie', base_price: 500, discount_price: 399, effective_price: 399, stock_quantity: 80, image: 'https://images.unsplash.com/photo-1576871337622-98d48d1cf531?w=600&q=80', category: { name: 'Accessories', slug: 'accessories' } },
  ],
  jacketProducts: [
    { id: 4, name: 'Leather Biker Jacket', slug: 'leather-biker-jacket', base_price: 5500, discount_price: 4200, effective_price: 4200, stock_quantity: 10, image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&q=80', category: { name: 'Jackets', slug: 'jackets' } },
    { id: 10, name: 'Bomber Jacket', slug: 'bomber-jacket', base_price: 4800, discount_price: 3800, effective_price: 3800, stock_quantity: 8, image: 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?w=600&q=80', category: { name: 'Jackets', slug: 'jackets' } },
    { id: 14, name: 'Denim Jacket', slug: 'denim-jacket', base_price: 4200, discount_price: 3500, effective_price: 3500, stock_quantity: 12, image: 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=600&q=80', category: { name: 'Jackets', slug: 'jackets' } },
    { id: 21, name: 'Puffer Jacket', slug: 'puffer-jacket', base_price: 6200, discount_price: 4900, effective_price: 4900, stock_quantity: 6, image: 'https://images.unsplash.com/photo-1604644401890-0bd678c83788?w=600&q=80', category: { name: 'Jackets', slug: 'jackets' } },
  ],
};

export default function Home() {
  const data = useMemo(() => staticData, []);

  return (
    <div>
      <HeroBanner />
      <CategoryGrid categories={data.categories} />
      <ProductSection title="BEST SELLING" products={data.bestSelling} initialCount={8} />
      <ProductSection title="NEW ARRIVAL" products={data.newArrivals} initialCount={8} />
      <CategoryBanner />
      <CategoryProducts title="Jackets Collection" products={data.jacketProducts} />
      <Features />
    </div>
  );
}
