import React, { useState, useMemo } from 'react';
import { Link } from 'react-router-dom';
import { ShoppingCart, Heart, Eye } from 'lucide-react';
import { useCart } from '../contexts/CartContext';
import { useWishlist } from '../contexts/WishlistContext';

const colorMap = {
  Black: '#1a1a1a', White: '#e5e7eb', Gray: '#6b7280', Navy: '#1e3a5f',
  Blue: '#2563eb', Green: '#16a34a', Red: '#dc2626', Brown: '#78350f',
  Beige: '#f5e6d3', Pink: '#ec4899', Purple: '#7c3aed', Yellow: '#eab308',
  Orange: '#f97316', Cream: '#fef3c7', Maroon: '#7f1d1d', Camel: '#c19a6b',
};

export default function ProductCard({ product }) {
  const { addToCart } = useCart();
  const { toggleWishlist, isWishlisted } = useWishlist();
  const {
    name, slug, base_price, discount_price, effective_price,
    image, category, stock_quantity, variants,
  } = product;

  const uniqueColors = useMemo(() => {
    if (!variants || variants.length === 0) return [];
    return [...new Set(variants.map((v) => v.color))];
  }, [variants]);

  const uniqueSizes = useMemo(() => {
    if (!variants || variants.length === 0) return [];
    return [...new Set(variants.map((v) => v.size))];
  }, [variants]);

  const [selectedColor, setSelectedColor] = useState(uniqueColors[0] || null);
  const [selectedSize, setSelectedSize] = useState(null);

  const activeVariant = useMemo(() => {
    if (!variants || variants.length === 0) return null;
    if (selectedColor && selectedSize) {
      return variants.find((v) => v.color === selectedColor && v.size === selectedSize) || null;
    }
    if (selectedColor) {
      return variants.find((v) => v.color === selectedColor) || null;
    }
    return null;
  }, [variants, selectedColor, selectedSize]);

  const displayPrice = activeVariant?.price ?? effective_price;
  const displayImage = activeVariant?.image ?? image;
  const displayStock = activeVariant?.stock ?? stock_quantity;
  const displayBasePrice = product.base_price;
  const hasDiscount = displayPrice < displayBasePrice;
  const isOutOfStock = displayStock === 0;
  const discountPercent = hasDiscount ? Math.round((1 - displayPrice / displayBasePrice) * 100) : 0;

  const cartItem = activeVariant
    ? { ...product, image: displayImage, price: displayPrice, effective_price: displayPrice, stock_quantity: displayStock, size: selectedSize, color: selectedColor }
    : product;

  const colorVariants = useMemo(() => {
    if (!variants) return [];
    if (!selectedSize) return [...new Map(variants.map((v) => [v.color, v])).values()];
    return variants.filter((v) => v.size === selectedSize).reduce((acc, v) => {
      if (!acc.find((a) => a.color === v.color)) acc.push(v);
      return acc;
    }, []);
  }, [variants, selectedSize]);

  const sizeVariants = useMemo(() => {
    if (!variants) return [];
    return variants.filter((v) => v.color === selectedColor).reduce((acc, v) => {
      if (!acc.find((a) => a.size === v.size)) acc.push(v);
      return acc;
    }, []);
  }, [variants, selectedColor]);

  const handleAddToCart = (e) => {
    e.preventDefault();
    e.stopPropagation();
    addToCart(cartItem);
  };

  const handleToggleWishlist = (e) => {
    e.preventDefault();
    e.stopPropagation();
    toggleWishlist(product);
  };

  return (
    <div className="group relative bg-white border border-gray-100 hover:border-gray-200 hover:shadow-lg transition-all duration-300">
      <Link to={`/products/${slug}`} className="block relative overflow-hidden bg-gray-50">
        <div className="aspect-[3/4]">
          {displayImage ? (
            <img
              src={displayImage}
              alt={name}
              className="w-full h-full object-cover group-hover:scale-105 transition duration-700"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-gray-200 text-4xl">?</div>
          )}
        </div>

        <div className="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-500" />

        <button
          onClick={handleToggleWishlist}
          className="absolute top-3 right-3 w-9 h-9 flex items-center justify-center bg-white shadow-sm hover:shadow-md transition-all duration-200 z-10 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0"
        >
          <Heart
            className={`w-4 h-4 transition ${
              isWishlisted(product.id) ? 'fill-red-500 text-red-500' : 'text-gray-700'
            }`}
          />
        </button>

        <Link
          to={`/products/${slug}`}
          className="absolute top-3 right-14 w-9 h-9 flex items-center justify-center bg-white shadow-sm hover:shadow-md transition-all duration-200 z-10 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0"
          style={{ transitionDelay: '50ms' }}
        >
          <Eye className="w-4 h-4 text-gray-700" />
        </Link>

        {hasDiscount && (
          <span className="absolute top-3 left-3 bg-red-500 text-white text-[10px] px-2 py-1 font-semibold tracking-wider z-10 shadow-sm">
            -{discountPercent}%
          </span>
        )}
        {isOutOfStock && (
          <span className="absolute top-3 left-3 bg-gray-900 text-white text-[10px] px-2 py-1 font-semibold tracking-wider z-10 shadow-sm">
            SOLD OUT
          </span>
        )}

        <div className="absolute inset-x-3 bottom-3 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 z-10">
          <button
            onClick={handleAddToCart}
            disabled={isOutOfStock}
            className="w-full bg-gray-900 text-white py-3 text-xs font-medium tracking-wider uppercase hover:bg-gray-800 transition shadow-lg flex items-center justify-center gap-2"
          >
            <ShoppingCart className="w-3.5 h-3.5" />
            {isOutOfStock ? 'Out of Stock' : 'Add to Cart'}
          </button>
        </div>
      </Link>

      <div className="p-3 md:p-4">
        <div className="flex items-center justify-between mb-1.5">
          {category && (
            <span className="text-[10px] text-gray-400 uppercase tracking-[0.15em] font-medium">
              {typeof category === 'string' ? category : category.name}
            </span>
          )}
          <div className="flex items-center gap-0.5">
            {[...Array(5)].map((_, i) => (
              <span key={i} className="w-2 h-2 rounded-full bg-gray-200" />
            ))}
          </div>
        </div>

        <Link to={`/products/${slug}`}>
          <h3 className="text-sm font-semibold text-gray-900 mb-2 line-clamp-1 leading-snug hover:text-gray-600 transition">
            {name}
          </h3>
        </Link>

        <div className="flex items-center gap-2 mb-2.5">
          <span className="text-sm font-bold text-gray-900">৳{displayPrice}</span>
          {hasDiscount && (
            <span className="text-xs text-gray-300 line-through">৳{displayBasePrice}</span>
          )}
        </div>

        {uniqueColors.length > 0 && (
          <div className="flex items-center gap-1.5 mb-0.5">
            {colorVariants.map((v) => (
              <button
                key={v.color}
                onClick={(e) => { e.preventDefault(); setSelectedColor(v.color); setSelectedSize(null); }}
                className={`w-[18px] h-[18px] rounded-full border-2 transition-all duration-200 ${
                  selectedColor === v.color
                    ? 'border-gray-900 scale-110 shadow-sm'
                    : 'border-gray-200 hover:border-gray-400'
                }`}
                style={{ backgroundColor: colorMap[v.color] || '#ccc' }}
                title={v.color}
              />
            ))}
          </div>
        )}

        {uniqueSizes.length > 0 && selectedColor && (
          <div className="flex items-center gap-1.5 mt-2 flex-wrap">
            {sizeVariants.map((v) => (
              <button
                key={v.size}
                onClick={(e) => { e.preventDefault(); setSelectedSize(selectedSize === v.size ? null : v.size); }}
                className={`text-[11px] px-2 py-0.5 font-medium transition-all duration-200 ${
                  selectedSize === v.size
                    ? 'bg-gray-900 text-white'
                    : 'bg-gray-50 text-gray-500 border border-gray-200 hover:border-gray-400 hover:bg-gray-100'
                }`}
              >
                {v.size}
              </button>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
