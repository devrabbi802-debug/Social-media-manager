import React, { useState, useEffect, useMemo } from 'react';
import { useParams, Link } from 'react-router-dom';
import { ShoppingCart, ChevronRight, Star, Minus, Plus, Heart } from 'lucide-react';
import { useCart } from '../contexts/CartContext';
import { useWishlist } from '../contexts/WishlistContext';
import api from '../../../api/client';

const colorMap = {
  Black: '#1a1a1a', White: '#e5e7eb', Gray: '#6b7280', Navy: '#1e3a5f',
  Blue: '#2563eb', Green: '#16a34a', Red: '#dc2626', Brown: '#78350f',
  Beige: '#f5e6d3', Pink: '#ec4899', Purple: '#7c3aed', Yellow: '#eab308',
  Orange: '#f97316', Cream: '#fef3c7', Maroon: '#7f1d1d', Camel: '#c19a6b',
};

export default function ProductDetail() {
  const { slug } = useParams();
  const { addToCart } = useCart();
  const { toggleWishlist, isWishlisted } = useWishlist();

  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedImage, setSelectedImage] = useState(0);
  const [selectedColor, setSelectedColor] = useState(null);
  const [selectedSize, setSelectedSize] = useState(null);
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        setLoading(true);
        setError(null);
        setSelectedImage(0);
        setSelectedColor(null);
        setSelectedSize(null);
        setQuantity(1);
        const response = await api.get(`/storefront/products/${slug}`);
        const data = response?.data || response;
        setProduct(data);
      } catch (err) {
        setError(err?.response?.status === 404 ? 'NOT_FOUND' : err.message);
      } finally {
        setLoading(false);
      }
    };
    fetchProduct();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }, [slug]);

  const variants = product?.variants || [];
  const images = product?.images?.length > 0 ? product.images : [];

  const uniqueColors = useMemo(() => {
    if (!variants.length) return [];
    return [...new Set(variants.map((v) => v.color).filter(Boolean))];
  }, [variants]);

  const uniqueSizes = useMemo(() => {
    if (!variants.length) return [];
    return [...new Set(variants.map((v) => v.size).filter(Boolean))];
  }, [variants]);

  const colorVariants = useMemo(() => {
    if (!variants.length) return [];
    if (!selectedSize) return [...new Map(variants.map((v) => [v.color, v])).values()];
    return variants.filter((v) => v.size === selectedSize).reduce((acc, v) => {
      if (!acc.find((a) => a.color === v.color)) acc.push(v);
      return acc;
    }, []);
  }, [variants, selectedSize]);

  const sizeVariants = useMemo(() => {
    if (!variants.length) return [];
    return variants.filter((v) => v.color === selectedColor).reduce((acc, v) => {
      if (!acc.find((a) => a.size === v.size)) acc.push(v);
      return acc;
    }, []);
  }, [variants, selectedColor]);

  const activeVariant = useMemo(() => {
    if (!variants.length) return null;
    if (selectedColor && selectedSize) {
      return variants.find((v) => v.color === selectedColor && v.size === selectedSize) || null;
    }
    if (selectedColor) {
      return variants.find((v) => v.color === selectedColor) || null;
    }
    return null;
  }, [variants, selectedColor, selectedSize]);

  const displayPrice = activeVariant?.price ?? product?.effective_price ?? product?.base_price ?? 0;
  const displayBasePrice = product?.base_price ?? 0;
  const displayStock = activeVariant?.stock ?? product?.stock_quantity ?? 0;
  const hasDiscount = product?.discount_price && product?.discount_price < product?.base_price;
  const isOutOfStock = displayStock === 0;
  const discountPercent = hasDiscount ? Math.round((1 - product.discount_price / product.base_price) * 100) : 0;

  const currentImageIndex = (() => {
    if (activeVariant?.image && images.length > 0) {
      const idx = images.indexOf(activeVariant.image);
      if (idx !== -1) return idx;
    }
    return selectedImage;
  })();

  if (loading) {
    return (
      <div className="pt-20">
        <div className="container mx-auto px-4 py-8">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <div>
              <div className="aspect-[4/5] bg-gray-100 rounded-xl animate-pulse mb-4" />
              <div className="flex gap-3">
                {[1, 2, 3].map((i) => (
                  <div key={i} className="w-20 h-20 bg-gray-100 rounded-lg animate-pulse" />
                ))}
              </div>
            </div>
            <div className="space-y-4">
              <div className="h-3 bg-gray-100 rounded w-24 animate-pulse" />
              <div className="h-8 bg-gray-100 rounded w-3/4 animate-pulse" />
              <div className="flex gap-1">
                {[1, 2, 3, 4, 5].map((i) => (
                  <div key={i} className="w-4 h-4 bg-gray-100 rounded animate-pulse" />
                ))}
              </div>
              <div className="h-10 bg-gray-100 rounded w-1/3 animate-pulse" />
              <div className="h-4 bg-gray-100 rounded w-1/4 animate-pulse" />
              <div className="h-12 bg-gray-100 rounded animate-pulse" />
              <div className="h-12 bg-gray-100 rounded animate-pulse" />
              <div className="h-12 bg-gray-100 rounded w-1/3 animate-pulse" />
              <div className="h-12 bg-gray-100 rounded animate-pulse" />
            </div>
          </div>
        </div>
      </div>
    );
  }

  if (error === 'NOT_FOUND' || !product) {
    return (
      <div className="pt-20">
        <div className="container mx-auto px-4 py-20 text-center">
          <div className="text-6xl mb-4">🔍</div>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Product Not Found</h2>
          <p className="text-gray-500 mb-6">The product you're looking for doesn't exist or has been removed.</p>
          <Link to="/products" className="inline-flex items-center gap-2 bg-gray-900 text-white px-6 py-3 text-sm font-medium hover:bg-gray-800 transition">
            ← Back to Products
          </Link>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="pt-20">
        <div className="container mx-auto px-4 py-20 text-center">
          <div className="text-6xl mb-4">⚠️</div>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Something went wrong</h2>
          <p className="text-gray-500 mb-6">{error}</p>
          <button onClick={() => window.location.reload()} className="inline-flex items-center gap-2 bg-gray-900 text-white px-6 py-3 text-sm font-medium hover:bg-gray-800 transition">
            Try Again
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="pt-20">
      <div className="container mx-auto px-4 py-8">
        <nav className="flex items-center gap-2 text-xs text-gray-400 mb-8 uppercase tracking-wider">
          <Link to="/" className="hover:text-gray-900 transition">Home</Link>
          <ChevronRight className="w-3 h-3" />
          <Link to="/products" className="hover:text-gray-900 transition">Products</Link>
          {product.category && (
            <>
              <ChevronRight className="w-3 h-3" />
              <Link to={`/category/${product.category.slug}`} className="hover:text-gray-900 transition">{product.category.name}</Link>
            </>
          )}
          <ChevronRight className="w-3 h-3" />
          <span className="text-gray-900 truncate max-w-[200px]">{product.name}</span>
        </nav>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
          <div>
            <div className="aspect-[4/5] bg-gray-50 rounded-xl overflow-hidden mb-4">
              {images.length > 0 ? (
                <img
                  src={images[currentImageIndex]}
                  alt={product.name}
                  className="w-full h-full object-cover"
                />
              ) : (
                <div className="w-full h-full flex items-center justify-center text-gray-200 text-6xl">?</div>
              )}
            </div>
            {images.length > 1 && (
              <div className="flex gap-3">
                {images.map((img, i) => (
                  <button
                    key={i}
                    onClick={() => setSelectedImage(i)}
                    className={`w-20 h-20 bg-gray-50 rounded-lg overflow-hidden border-2 transition flex-shrink-0 ${
                      currentImageIndex === i ? 'border-gray-900 shadow-md' : 'border-transparent hover:border-gray-300'
                    }`}
                  >
                    <img src={img} alt="" className="w-full h-full object-cover" />
                  </button>
                ))}
              </div>
            )}
          </div>

          <div className="flex flex-col">
            {product.category && (
              <span className="text-xs uppercase tracking-[0.2em] text-gray-400 mb-2">
                {product.category.name}
              </span>
            )}
            <h1 className="text-2xl md:text-3xl font-bold text-gray-900 mb-3">{product.name}</h1>

            <div className="flex items-center gap-2 mb-4">
              <div className="flex items-center gap-0.5">
                {[...Array(5)].map((_, i) => (
                  <Star key={i} className="w-4 h-4 fill-amber-400 text-amber-400" />
                ))}
              </div>
              <span className="text-xs text-gray-400">(0 reviews)</span>
            </div>

            <div className="flex items-baseline gap-3 mb-6">
              <span className="text-3xl font-bold text-gray-900">৳{displayPrice}</span>
              {hasDiscount && (
                <>
                  <span className="text-lg text-gray-300 line-through">৳{product.base_price}</span>
                  <span className="bg-red-50 text-red-500 text-xs px-2 py-0.5 font-medium rounded">
                    -{discountPercent}%
                  </span>
                </>
              )}
            </div>

            <p className={`text-xs font-medium mb-6 flex items-center gap-1.5 ${isOutOfStock ? 'text-red-500' : 'text-green-600'}`}>
              <span className={`w-1.5 h-1.5 rounded-full ${isOutOfStock ? 'bg-red-500' : 'bg-green-600'}`} />
              {isOutOfStock ? 'Out of Stock' : `In Stock (${displayStock} available)`}
            </p>

            {uniqueColors.length > 0 && (
              <div className="mb-6">
                <h4 className="text-xs font-bold uppercase tracking-wider mb-3">
                  Color: <span className="text-gray-900">{selectedColor || uniqueColors[0]}</span>
                </h4>
                <div className="flex gap-2">
                  {colorVariants.map((v) => (
                    <button
                      key={v.color}
                      onClick={() => { setSelectedColor(v.color); setSelectedSize(null); }}
                      className={`w-10 h-10 rounded-full border-2 transition-all duration-200 ${
                        selectedColor === v.color
                          ? 'border-gray-900 ring-2 ring-gray-900 ring-offset-2 scale-110'
                          : 'border-gray-200 hover:border-gray-400'
                      }`}
                      style={{ backgroundColor: colorMap[v.color] || '#ccc' }}
                      title={v.color}
                    />
                  ))}
                </div>
              </div>
            )}

            {uniqueSizes.length > 0 && (
              <div className="mb-6">
                <h4 className="text-xs font-bold uppercase tracking-wider mb-3">
                  Size: <span className="text-gray-900">{selectedSize || 'Select'}</span>
                </h4>
                <div className="flex gap-2 flex-wrap">
                  {(selectedColor ? sizeVariants : variants).filter((v, i, a) => a.findIndex((x) => x.size === v.size) === i).map((v) => (
                    <button
                      key={v.size}
                      onClick={() => setSelectedSize(selectedSize === v.size ? null : v.size)}
                      className={`w-12 h-10 text-xs font-medium rounded-md border transition-all duration-200 ${
                        selectedSize === v.size
                          ? 'border-gray-900 bg-gray-900 text-white shadow-sm'
                          : 'border-gray-200 text-gray-600 hover:border-gray-900 hover:text-gray-900'
                      }`}
                    >
                      {v.size}
                    </button>
                  ))}
                </div>
              </div>
            )}

            <div className="mb-8">
              <h4 className="text-xs font-bold uppercase tracking-wider mb-3">Quantity</h4>
              <div className="flex items-center border border-gray-200 rounded-md w-fit overflow-hidden">
                <button
                  onClick={() => setQuantity(Math.max(1, quantity - 1))}
                  className="w-10 h-10 flex items-center justify-center hover:bg-gray-50 transition text-gray-600"
                >
                  <Minus className="w-3 h-3" />
                </button>
                <span className="w-12 h-10 flex items-center justify-center text-sm font-medium border-x border-gray-200">
                  {quantity}
                </span>
                <button
                  onClick={() => setQuantity(Math.min(displayStock || 99, quantity + 1))}
                  className="w-10 h-10 flex items-center justify-center hover:bg-gray-50 transition text-gray-600"
                >
                  <Plus className="w-3 h-3" />
                </button>
              </div>
            </div>

            <div className="flex gap-3 mb-8">
              <button
                onClick={() => {
                  for (let i = 0; i < quantity; i++) {
                    addToCart({
                      ...product,
                      image: activeVariant?.image || images[0] || product.image,
                      price: displayPrice,
                      effective_price: displayPrice,
                      size: selectedSize,
                      color: selectedColor,
                    });
                  }
                }}
                disabled={isOutOfStock}
                className="flex-1 flex items-center justify-center gap-2 bg-gray-900 text-white py-3 px-6 text-sm font-medium hover:bg-gray-800 transition uppercase tracking-wider rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <ShoppingCart className="w-4 h-4" />
                {isOutOfStock ? 'Out of Stock' : 'Add to Cart'}
              </button>
              <button
                onClick={() => toggleWishlist(product)}
                className="w-12 h-12 border border-gray-200 rounded-lg flex items-center justify-center hover:border-gray-900 transition group"
              >
                <Heart
                  className={`w-4 h-4 transition ${
                    isWishlisted(product.id) ? 'fill-red-500 text-red-500' : 'text-gray-600 group-hover:text-red-500'
                  }`}
                />
              </button>
            </div>

            {product.description && (
              <div className="border-t border-gray-100 pt-6">
                <h4 className="text-xs font-bold uppercase tracking-wider mb-3">Description</h4>
                <p className="text-sm text-gray-500 leading-relaxed">{product.description}</p>
              </div>
            )}

            {product.attributes?.length > 0 && (
              <div className="border-t border-gray-100 pt-6 mt-4">
                <h4 className="text-xs font-bold uppercase tracking-wider mb-3">Details</h4>
                <dl className="space-y-2">
                  {product.attributes.map((attr, i) => (
                    <div key={i} className="flex text-sm">
                      <dt className="text-gray-400 w-32">{attr.attribute}</dt>
                      <dd className="text-gray-900 font-medium">{attr.value}</dd>
                    </div>
                  ))}
                </dl>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
