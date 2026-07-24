import React, { useState, useMemo } from 'react';
import { Link } from 'react-router-dom';
import { ShoppingCart, Heart, Eye, Star } from 'lucide-react';
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

  const hasVariants = variants && variants.length > 0;

  const activeVariant = useMemo(() => {
    if (!variants || variants.length === 0) return null;
    if (selectedColor && selectedSize) {
      return variants.find((v) => v.color === selectedColor && v.size === selectedSize) || null;
    }
    if (selectedColor) {
      return variants.find((v) => v.color === selectedColor) || null;
    }
    if (selectedSize) {
      return variants.find((v) => v.size === selectedSize) || null;
    }
    return null;
  }, [variants, selectedColor, selectedSize]);

  const variantSelectionComplete = useMemo(() => {
    if (!hasVariants) return true;
    const needsColor = uniqueColors.length > 0;
    const needsSize = uniqueSizes.length > 0;
    if (needsColor && needsSize) return !!selectedColor && !!selectedSize;
    if (needsColor) return !!selectedColor;
    if (needsSize) return !!selectedSize;
    return true;
  }, [hasVariants, uniqueColors, uniqueSizes, selectedColor, selectedSize]);

  const displayPrice = activeVariant?.price ?? effective_price;
  const displayImage = activeVariant?.image ?? image;
  const displayStock = activeVariant?.stock ?? stock_quantity;
  const displayBasePrice = product.base_price;
  const hasDiscount = displayPrice < displayBasePrice;
  const isOutOfStock = displayStock === 0;
  const discountPercent = hasDiscount ? Math.round((1 - displayPrice / displayBasePrice) * 100) : 0;

  const cartItem = activeVariant
    ? { ...product, image: displayImage, price: displayPrice, effective_price: displayPrice, stock_quantity: displayStock, variant_id: activeVariant.id, size: selectedSize, color: selectedColor }
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
    <div className="group relative bg-white rounded-xl shadow-sm hover:shadow-xl border border-gray-100 hover:border-gray-200 transition-all duration-300 hover:-translate-y-1">
      <Link to={`/products/${slug}`} className="block relative overflow-hidden rounded-t-xl bg-gray-50">
        <div className="aspect-[3/4]">
          {displayImage ? (
            <img
              src={displayImage}
              alt={name}
              className="w-full h-full object-cover group-hover:scale-110 transition duration-700"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-gray-200 text-4xl">?</div>
          )}
        </div>

        <div className="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />

        <button
          onClick={handleToggleWishlist}
          className="absolute top-3 right-3 w-9 h-9 flex items-center justify-center bg-white/90 backdrop-blur-sm rounded-full shadow-lg hover:bg-white hover:scale-110 transition-all duration-200 z-10"
        >
          <Heart
            className={`w-[18px] h-[18px] transition ${
              isWishlisted(product.id) ? 'fill-red-500 text-red-500' : 'text-gray-700'
            }`}
          />
        </button>

        <Link
          to={`/products/${slug}`}
          className="absolute top-3 right-14 w-9 h-9 flex items-center justify-center bg-white/90 backdrop-blur-sm rounded-full shadow-lg hover:bg-white hover:scale-110 transition-all duration-200 z-10 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0"
          style={{ transitionDelay: '50ms' }}
        >
          <Eye className="w-[18px] h-[18px] text-gray-700" />
        </Link>

        {hasDiscount && (
          <span className="absolute top-3 left-3 bg-gradient-to-r from-red-500 to-rose-500 text-white text-[10px] px-2.5 py-1 font-bold tracking-wider z-10 shadow-lg rounded-full">
            -{discountPercent}%
          </span>
        )}
        {isOutOfStock && (
          <span className="absolute top-3 left-3 bg-gray-900/90 backdrop-blur-sm text-white text-[10px] px-2.5 py-1 font-bold tracking-wider z-10 shadow-lg rounded-full">
            SOLD OUT
          </span>
        )}

        <div className="absolute inset-x-3 bottom-3 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 z-10">
          <button
            onClick={handleAddToCart}
            disabled={isOutOfStock || !variantSelectionComplete}
            className="w-full bg-white text-gray-900 py-3 text-xs font-semibold tracking-wider uppercase hover:bg-gray-900 hover:text-white transition-all duration-300 shadow-lg rounded-lg flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <ShoppingCart className="w-3.5 h-3.5" />
            {isOutOfStock ? 'Out of Stock' : !variantSelectionComplete ? 'Select Variant' : 'Add to Cart'}
          </button>
        </div>
      </Link>

      <div className="p-4">
        <div className="flex items-center justify-between mb-2">
          {category && (
            <span className="text-[10px] text-gray-400 uppercase tracking-[0.15em] font-medium">
              {typeof category === 'string' ? category : category.name}
            </span>
          )}
          <div className="flex items-center gap-0.5">
            {[...Array(5)].map((_, i) => (
              <Star key={i} className="w-3 h-3 fill-gray-200 text-gray-200" />
            ))}
          </div>
        </div>

        <Link to={`/products/${slug}`}>
          <h3 className="text-sm font-semibold text-gray-900 mb-2.5 line-clamp-1 leading-snug hover:text-primary transition-colors">
            {name}
          </h3>
        </Link>

        <div className="flex items-center gap-2 mb-3">
          <span className="text-lg font-bold text-gray-900">৳{displayPrice}</span>
          {hasDiscount && (
            <span className="text-sm text-gray-400 line-through">৳{displayBasePrice}</span>
          )}
        </div>

        {uniqueColors.length > 0 && (
          <div className="flex items-center gap-2 mb-0.5">
            {colorVariants.map((v) => (
              <button
                key={v.color}
                onClick={(e) => { e.preventDefault(); setSelectedColor(v.color); setSelectedSize(null); }}
                className={`w-5 h-5 rounded-full transition-all duration-200 ${
                  selectedColor === v.color
                    ? 'ring-2 ring-gray-900 ring-offset-2 scale-110'
                    : 'ring-1 ring-gray-300 hover:ring-gray-400'
                }`}
                style={{ backgroundColor: colorMap[v.color] || '#ccc' }}
                title={v.color}
              />
            ))}
          </div>
        )}

        {uniqueSizes.length > 0 && selectedColor && (
          <div className="flex items-center gap-1.5 mt-2.5 flex-wrap">
            {sizeVariants.map((v) => (
              <button
                key={v.size}
                onClick={(e) => { e.preventDefault(); setSelectedSize(selectedSize === v.size ? null : v.size); }}
                className={`text-[11px] px-2.5 py-1 font-medium rounded-md transition-all duration-200 ${
                  selectedSize === v.size
                    ? 'bg-gray-900 text-white shadow-sm'
                    : 'bg-gray-50 text-gray-600 border border-gray-200 hover:border-gray-900 hover:text-gray-900'
                }`}
              >
                {v.size}
              </button>
            ))}
          </div>
        )}

        {(selectedColor || selectedSize) && (
          <p className="text-[10px] text-gray-500 mt-2">
            {[selectedColor, selectedSize].filter(Boolean).join(' / ')}
          </p>
        )}
      </div>
    </div>
  );
}
