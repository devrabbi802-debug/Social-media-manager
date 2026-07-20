import React from 'react';
import HeroBanner from '../components/HeroBanner';
import CategoryGrid from '../components/CategoryGrid';
import CategorySlider from '../components/CategorySlider';
import ProductSection from '../components/ProductSection';
import CategoryBanner from '../components/CategoryBanner';
import CategoryProducts from '../components/CategoryProducts';
import Features from '../components/Features';
import { allProducts } from '../data/products';

const categories = [
  { id: 1, name: 'T-Shirts', slug: 't-shirts', image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&q=80', products_count: 24 },
  { id: 2, name: 'Denim', slug: 'denim', image: 'https://images.unsplash.com/photo-1542272454315-4c01d7abdf4a?w=800&q=80', products_count: 18 },
  { id: 3, name: 'Hoodies', slug: 'hoodies', image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=800&q=80', products_count: 15 },
  { id: 4, name: 'Jackets', slug: 'jackets', image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=800&q=80', products_count: 12 },
  { id: 5, name: 'Shoes', slug: 'shoes', image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&q=80', products_count: 30 },
  { id: 6, name: 'Accessories', slug: 'accessories', image: 'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?w=800&q=80', products_count: 20 },
];

const jacketIds = [4, 10, 14, 21];

const bestSelling = allProducts.slice(0, 10);
const newArrivals = allProducts.slice(10, 20);
const jacketProducts = allProducts.filter((p) => jacketIds.includes(p.id));

export default function Home() {
  return (
    <div>
      <HeroBanner />
      <CategoryGrid categories={categories} />
      <CategorySlider categories={categories} />
      <ProductSection title="BEST SELLING" products={bestSelling} initialCount={8} />
      <ProductSection title="NEW ARRIVAL" products={newArrivals} initialCount={8} />
      <CategoryBanner />
      <CategoryProducts title="Jackets Collection" products={jacketProducts} />
      <Features />
    </div>
  );
}
