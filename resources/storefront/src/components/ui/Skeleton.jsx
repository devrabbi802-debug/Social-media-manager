import React from 'react';

export function Skeleton({ className = '' }) {
  return (
    <div className={`animate-pulse bg-gray-200 rounded ${className}`} />
  );
}

export function ProductCardSkeleton() {
  return (
    <div className="card">
      <Skeleton className="aspect-square" />
      <div className="p-4">
        <Skeleton className="h-4 w-1/3 mb-2" />
        <Skeleton className="h-6 w-full mb-2" />
        <Skeleton className="h-4 w-2/3" />
      </div>
    </div>
  );
}

export function BannerSkeleton() {
  return (
    <Skeleton className="h-96 w-full" />
  );
}