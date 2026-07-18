import React from 'react';
import { ShoppingCart } from 'lucide-react';

export default function ProductCard({ product }) {
  const {
    name,
    slug,
    base_price,
    discount_price,
    effective_price,
    image,
    category,
    brand,
    stock_quantity,
  } = product;

  const hasDiscount = discount_price && discount_price < base_price;
  const isOutOfStock = stock_quantity === 0;

  return (
    <div className="card-hover group">
      <a href={`/products/${slug}`}>
        <div className="relative aspect-square bg-gray-100 overflow-hidden">
          {image ? (
            <img
              src={`/storage/${image}`}
              alt={name}
              className="w-full h-full object-cover group-hover:scale-105 transition duration-300"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-gray-400">
              <span className="text-4xl">📷</span>
            </div>
          )}
          
          {/* Badges */}
          <div className="absolute top-2 left-2 flex flex-col gap-1">
            {hasDiscount && (
              <span className="bg-red-500 text-white text-xs px-2 py-1 rounded">
                -{Math.round((1 - discount_price / base_price) * 100)}%
              </span>
            )}
            {isOutOfStock && (
              <span className="bg-gray-500 text-white text-xs px-2 py-1 rounded">
                Out of Stock
              </span>
            )}
          </div>
        </div>
      </a>

      <div className="p-4">
        {/* Category & Brand */}
        <div className="flex items-center gap-2 text-xs text-gray-500 mb-2">
          {category && <span>{category.name}</span>}
          {category && brand && <span>•</span>}
          {brand && <span>{brand.name}</span>}
        </div>

        {/* Name */}
        <a href={`/products/${slug}`}>
          <h3 className="font-medium text-gray-900 hover:text-primary transition line-clamp-2 mb-2">
            {name}
          </h3>
        </a>

        {/* Price */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <span className="text-lg font-bold text-primary">
              ৳{effective_price}
            </span>
            {hasDiscount && (
              <span className="text-sm text-gray-400 line-through">
                ৳{base_price}
              </span>
            )}
          </div>
          
          <button
            className="p-2 bg-primary/10 text-primary rounded-full hover:bg-primary hover:text-white transition"
            disabled={isOutOfStock}
          >
            <ShoppingCart className="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  );
}