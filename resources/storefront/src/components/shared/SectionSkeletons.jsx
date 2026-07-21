import React from 'react';
import Skeleton from './Skeleton';

export function HeroBannerSkeleton() {
  return (
    <div className="w-full h-[80vh] min-h-[500px] max-h-[800px] bg-gray-100 overflow-hidden relative">
      <Skeleton className="absolute inset-0" />
      <div className="absolute inset-0 flex flex-col items-center justify-center gap-4 px-6">
        <Skeleton className="h-4 w-32 rounded" />
        <Skeleton className="h-10 w-72 rounded" />
        <Skeleton className="h-5 w-48 rounded" />
        <Skeleton className="h-12 w-36 rounded-full mt-4" />
      </div>
    </div>
  );
}

export function CategoryGridSkeleton() {
  return (
    <section className="px-4 pt-4 md:pt-6 pb-2">
      <div className="flex flex-col md:flex-row gap-[2px] md:h-[600px]">
        <div className="flex flex-col gap-[2px] flex-1">
          <SkeletonCard />
          <SkeletonCard />
        </div>
        <div className="flex-1 md:flex-[1.5]">
          <SkeletonCard />
        </div>
        <div className="flex flex-col gap-[2px] flex-1">
          <SkeletonCard />
          <SkeletonCard />
        </div>
      </div>
    </section>
  );
}

function SkeletonCard() {
  return (
    <div className="relative block overflow-hidden bg-gray-100 min-h-[200px] h-full">
      <Skeleton className="absolute inset-0" />
      <div className="absolute inset-0 flex flex-col items-center justify-end p-4 md:p-6">
        <Skeleton className="h-4 w-24 rounded mb-1" />
        <Skeleton className="h-3 w-16 rounded" />
      </div>
    </div>
  );
}

export function CategorySliderSkeleton() {
  return (
    <section className="py-8 md:py-12">
      <div className="container mx-auto px-4">
        <Skeleton className="h-6 w-40 rounded mx-auto mb-8" />
        <div className="flex gap-3">
          {[1, 2, 3, 4, 5].map((i) => (
            <div key={i} className="flex-1">
              <div className="relative bg-gray-100 overflow-hidden aspect-[4/5]">
                <Skeleton className="absolute inset-0" />
                <div className="absolute bottom-0 left-0 right-0 p-3">
                  <Skeleton className="h-4 w-20 rounded mx-auto" />
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

export function ProductGridSkeleton() {
  return (
    <section className="py-8 md:py-12">
      <div className="container mx-auto px-4">
        <Skeleton className="h-6 w-40 rounded mx-auto mb-8" />
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
          {[1, 2, 3, 4].map((i) => (
            <ProductCardSkeleton key={i} />
          ))}
        </div>
      </div>
    </section>
  );
}

export function ProductCardSkeleton() {
  return (
    <div className="bg-white">
      <Skeleton className="aspect-square w-full rounded-none" />
      <div className="p-3 space-y-2">
        <Skeleton className="h-4 w-3/4 rounded" />
        <Skeleton className="h-3 w-1/2 rounded" />
        <Skeleton className="h-5 w-1/3 rounded" />
      </div>
    </div>
  );
}

export function CategoryBannerSkeleton() {
  return (
    <section className="py-8 md:py-12">
      <div className="container mx-auto px-4">
        <div className="relative block overflow-hidden bg-gray-100 rounded-none">
          <Skeleton className="h-[250px] md:h-[350px] w-full" />
          <div className="absolute inset-0 flex items-center">
            <div className="px-6 md:px-12 max-w-lg space-y-3">
              <Skeleton className="h-3 w-24 rounded" />
              <Skeleton className="h-8 w-64 rounded" />
              <Skeleton className="h-4 w-48 rounded" />
              <Skeleton className="h-5 w-28 rounded" />
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

export function BrandShowcaseSkeleton() {
  return (
    <section className="py-12 bg-gray-50">
      <div className="container mx-auto px-4">
        <Skeleton className="h-7 w-40 rounded mx-auto mb-8" />
        <div className="flex flex-wrap justify-center items-center gap-8">
          {[1, 2, 3, 4, 5].map((i) => (
            <div key={i} className="bg-white rounded-lg p-6 shadow-sm border border-gray-100">
              <Skeleton className="h-12 w-28 rounded" />
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

export function NewsletterSkeleton() {
  return (
    <section className="py-12 bg-gray-50">
      <div className="container mx-auto px-4">
        <div className="max-w-xl mx-auto text-center space-y-4">
          <Skeleton className="h-7 w-40 rounded mx-auto" />
          <Skeleton className="h-5 w-72 rounded mx-auto" />
          <div className="flex gap-2">
            <Skeleton className="flex-1 h-12 rounded-lg" />
            <Skeleton className="w-28 h-12 rounded-lg" />
          </div>
        </div>
      </div>
    </section>
  );
}

export function PromoBannerSkeleton() {
  return (
    <section className="py-12">
      <div className="container mx-auto px-4">
        <div className="bg-gray-100 rounded-xl p-8 md:p-12">
          <div className="max-w-2xl space-y-4">
            <Skeleton className="h-8 w-56 rounded" />
            <Skeleton className="h-5 w-72 rounded" />
            <Skeleton className="h-12 w-32 rounded-lg" />
          </div>
        </div>
      </div>
    </section>
  );
}
