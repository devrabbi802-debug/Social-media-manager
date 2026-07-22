import React, { useState, useEffect } from 'react';
import { useEditor } from '../../../components/editor/EditorContext';
import EditableSection from '../../../components/editor/EditableSection';
import HeroBanner from '../components/HeroBanner';
import CategoryGrid from '../components/CategoryGrid';
import CategorySlider from '../components/CategorySlider';
import ProductSection from '../components/ProductSection';
import CategoryBanner from '../components/CategoryBanner';
import CategoryProducts from '../components/CategoryProducts';
import Features from '../components/Features';
import { allProducts } from '../data/products';
import api from '../../../api/client';

const defaultCategories = [
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
  const { isEditorMode } = useEditor();
  const [banners, setBanners] = useState(null);
  const [featuredCategories, setFeaturedCategories] = useState(null);
  const [allCategories, setAllCategories] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saveVer, setSaveVer] = useState(0);

  const gridCategories = featuredCategories && featuredCategories.length > 0 ? featuredCategories.slice(0, 5) : defaultCategories;
  const sliderCategories = allCategories && allCategories.length > 0 ? allCategories : defaultCategories;

  useEffect(() => {
    if (isEditorMode && window.__editor_banners && window.__editor_categories && window.__editor_all_categories) {
      setBanners(window.__editor_banners);
      setFeaturedCategories(window.__editor_categories.slice(0, 5));
      setAllCategories(window.__editor_all_categories);
      setLoading(false);
      return;
    }

    const endpoint = isEditorMode ? '/editor/sections' : '/storefront/home';
    api.get(endpoint).then((res) => {
      setBanners(res.banners || []);
      setFeaturedCategories(res.categories ? res.categories.slice(0, 5) : null);
      setAllCategories(res.all_categories || null);
    }).catch(() => {
      setBanners([]);
    }).finally(() => {
      setLoading(false);
    });
  }, [isEditorMode, saveVer]);

  const handleBannersSaved = (newBanners) => {
    window.__editor_banners = newBanners;
    setBanners(newBanners);
    setSaveVer((v) => v + 1);
  };

  const handleCategoriesSaved = (newCategories) => {
    window.__editor_categories = newCategories;
    setFeaturedCategories(newCategories.slice(0, 5));
    setSaveVer((v) => v + 1);
  };

  const handleAllCategoriesSaved = (newCategories) => {
    window.__editor_all_categories = newCategories;
    setAllCategories(newCategories);
    setSaveVer((v) => v + 1);
  };

  const bannerSectionData = {
    banners: banners,
    onBannersSaved: handleBannersSaved,
  };

  const gridSectionData = {
    categories: gridCategories,
    onCategoriesSaved: handleCategoriesSaved,
  };

  const sliderSectionData = {
    allCategories: sliderCategories,
    onAllCategoriesSaved: handleAllCategoriesSaved,
  };

  return (
    <div>
      <EditableSection sectionType="banners" sectionData={bannerSectionData} label="Slider">
        <HeroBanner banners={banners} />
      </EditableSection>
      <EditableSection sectionType="category-grid" sectionData={gridSectionData} label="Categories">
        <CategoryGrid categories={gridCategories} loading={loading} />
      </EditableSection>
      <EditableSection sectionType="all-categories" sectionData={sliderSectionData} label="All Categories">
        <CategorySlider categories={sliderCategories} loading={loading} />
      </EditableSection>
      <EditableSection sectionType="best-selling" sectionData={{ products: bestSelling }} label="Best Selling">
        <ProductSection title="BEST SELLING" products={bestSelling} initialCount={8} loading={loading} />
      </EditableSection>
      <EditableSection sectionType="new-arrival" sectionData={{ products: newArrivals }} label="New Arrival">
        <ProductSection title="NEW ARRIVAL" products={newArrivals} initialCount={8} loading={loading} />
      </EditableSection>
      <EditableSection sectionType="category-banner" sectionData={{}} label="Promo Banner">
        <CategoryBanner loading={loading} />
      </EditableSection>
      <EditableSection sectionType="category-products" sectionData={{ products: jacketProducts }} label="Category Products">
        <CategoryProducts title="Jackets Collection" products={jacketProducts} loading={loading} />
      </EditableSection>
      <EditableSection sectionType="features" sectionData={{}} label="Features">
        <Features />
      </EditableSection>
    </div>
  );
}