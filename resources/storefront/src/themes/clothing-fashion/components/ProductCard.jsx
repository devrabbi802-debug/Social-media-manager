import React from 'react';
import { Link } from 'react-router-dom';
import { ShoppingCart, Star, Heart } from 'lucide-react';
import { useCart } from '../contexts/CartContext';
import { useWishlist } from '../contexts/WishlistContext';

export default function ProductCard({ product }) {
  const { addToCart } = useCart();
  const { toggleWishlist, isWishlisted } = useWishlist();
  const {
    name, slug, base_price, discount_price, effective_price,
    image, category, stock_quantity,
  } = product;

  const hasDiscount = discount_price && discount_price < base_price;
  const isOutOfStock = stock_quantity === 0;
  const discountPercent = hasDiscount ? Math.round((1 - discount_price / base_price) * 100) : 0;

  return (
    <div className="group relative bg-white">
      <Link to={`/products/${slug}`} className="block relative overflow-hidden bg-gray-50">
        <div className="aspect-[3/4]">
          {image ? (
            <img
              src={image}
              alt={name}
              className="w-full h-full object-cover group-hover:scale-105 transition duration-700"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-gray-200 text-4xl">
              ?
            </div>
          )}
        </div>

        <div className="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-500" />

        <button
          onClick={(e) => { e.preventDefault(); toggleWishlist(product); }}
          className="absolute top-3 right-3 w-8 h-8 flex items-center justify-center bg-white/80 hover:bg-white transition z-10"
        >
          <Heart
            className={`w-4 h-4 transition ${
              isWishlisted(product.id) ? 'fill-red-500 text-red-500' : 'text-gray-600'
            }`}
          />
        </button>

        {hasDiscount && (
          <span className="absolute top-3 left-3 bg-red-500 text-white text-[10px] px-2 py-0.5 font-semibold tracking-wider z-10">
            -{discountPercent}%
          </span>
        )}
        {isOutOfStock && (
          <span className="absolute top-3 left-3 bg-gray-900 text-white text-[10px] px-2 py-0.5 font-semibold tracking-wider z-10">
            SOLD OUT
          </span>
        )}

        <div className="absolute bottom-0 left-0 right-0 p-3 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
          <button
            onClick={(e) => { e.preventDefault(); addToCart(product); }}
            disabled={isOutOfStock}
            className="w-full bg-white text-gray-900 py-2.5 text-xs font-medium tracking-wider uppercase hover:bg-gray-100 transition flex items-center justify-center gap-2"
          >
            <ShoppingCart className="w-3.5 h-3.5" />
            Add to Cart
          </button>
        </div>
      </Link>

      <div className="pt-3 pb-1">
        {category && (
          <span className="text-[10px] text-gray-400 uppercase tracking-[0.15em]">
            {category.name}
          </span>
        )}
        <Link to={`/products/${slug}`}>
          <h3 className="text-sm font-medium text-gray-900 mt-0.5 mb-1 line-clamp-2 leading-snug hover:text-gray-500 transition">
            {name}
          </h3>
        </Link>
        <div className="flex items-center gap-0.5 mb-1.5">
          {[...Array(5)].map((_, i) => (
            <Star key={i} className="w-3 h-3 fill-gray-900 text-gray-900" />
          ))}
          <span className="text-[10px] text-gray-300 ml-1">(12)</span>
        </div>
        <div className="flex items-center gap-2">
          <span className="text-sm font-bold text-gray-900">৳{effective_price}</span>
          {hasDiscount && (
            <span className="text-xs text-gray-300 line-through">৳{base_price}</span>
          )}
        </div>
      </div>
    </div>
  );
}
