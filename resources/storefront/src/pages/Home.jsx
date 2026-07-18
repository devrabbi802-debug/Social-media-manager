import React, { useState, useEffect } from 'react';
import api from '../api/client';
import HeroBanner from '../components/home/HeroBanner';
import CategoryGrid from '../components/home/CategoryGrid';
import FeaturedProducts from '../components/home/FeaturedProducts';
import BrandShowcase from '../components/home/BrandShowcase';
import PromoBanner from '../components/home/PromoBanner';
import Newsletter from '../components/home/Newsletter';
import { ProductCardSkeleton, BannerSkeleton } from '../components/ui/Skeleton';

export default function Home() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchHomeData = async () => {
      try {
        setLoading(true);
        const response = await api.get('/storefront/home');
        setData(response.data || response);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchHomeData();
  }, []);

  if (loading) {
    return (
      <div>
        <BannerSkeleton />
        <div className="container mx-auto px-4 py-12">
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            {[...Array(8)].map((_, i) => (
              <ProductCardSkeleton key={i} />
            ))}
          </div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container mx-auto px-4 py-12 text-center">
        <p className="text-red-500">Error loading store data: {error}</p>
      </div>
    );
  }

  return (
    <div>
      {/* Hero Banner */}
      <HeroBanner banners={data?.banners || []} />

      {/* Category Grid */}
      <CategoryGrid categories={data?.categories || []} />

      {/* Featured Products */}
      <FeaturedProducts products={data?.featured_products || []} />

      {/* Brand Showcase */}
      <BrandShowcase brands={data?.brands || []} />

      {/* Promo Banner */}
      <PromoBanner />

      {/* Newsletter */}
      <Newsletter />
    </div>
  );
}