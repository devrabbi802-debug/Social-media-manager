import React, { useState, useEffect } from 'react';
import api from '../../../api/client';
import HeroBanner from '../components/HeroBanner';
import CategoryGrid from '../components/CategoryGrid';
import FeaturedProducts from '../components/FeaturedProducts';
import BrandShowcase from '../components/BrandShowcase';
import PromoBanner from '../components/PromoBanner';
import Newsletter from '../components/Newsletter';

function ProductCardSkeleton() {
  return (
    <div className="card">
      <div className="aspect-square animate-pulse bg-gray-200 rounded" />
      <div className="p-4">
        <div className="h-4 bg-gray-200 rounded w-1/3 mb-2 animate-pulse" />
        <div className="h-6 bg-gray-200 rounded w-full mb-2 animate-pulse" />
        <div className="h-4 bg-gray-200 rounded w-2/3 animate-pulse" />
      </div>
    </div>
  );
}

function BannerSkeleton() {
  return <div className="h-96 w-full animate-pulse bg-gray-200 rounded" />;
}

export default function Home() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchHomeData = async () => {
      try {
        setLoading(true);
        const response = await api.get('/storefront/home');
        setData(response);
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
            {[...Array(8)].map((_, i) => <ProductCardSkeleton key={i} />)}
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
      <HeroBanner banners={data?.banners || []} />
      <CategoryGrid categories={data?.categories || []} />
      <FeaturedProducts products={data?.featured_products || []} />
      <BrandShowcase brands={data?.brands || []} />
      <PromoBanner />
      <Newsletter />
    </div>
  );
}
