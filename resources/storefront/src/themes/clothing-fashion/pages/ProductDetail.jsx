import React, { useState, useEffect, useMemo } from 'react';
import { useParams, Link } from 'react-router-dom';
import { ShoppingCart, ChevronRight, Star, Minus, Plus, Heart, MessageSquare, Ruler, Package, Tag, ShieldCheck } from 'lucide-react';
import { useCart } from '../contexts/CartContext';
import { useWishlist } from '../contexts/WishlistContext';
import ProductSection from '../components/ProductSection';
import ImageZoom from '../../../components/shared/ImageZoom';
import api from '../../../api/client';

const colorMap = {
  Black: '#1a1a1a', White: '#e5e7eb', Gray: '#6b7280', Navy: '#1e3a5f',
  Blue: '#2563eb', Green: '#16a34a', Red: '#dc2626', Brown: '#78350f',
  Beige: '#f5e6d3', Pink: '#ec4899', Purple: '#7c3aed', Yellow: '#eab308',
  Orange: '#f97316', Cream: '#fef3c7', Maroon: '#7f1d1d', Camel: '#c19a6b',
};

const hexColorRegex = /^#[0-9a-fA-F]{3,8}$/;

function resolveColor(value) {
  if (hexColorRegex.test(value)) return value;
  return colorMap[value] || '#ccc';
}

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
  const [activeTab, setActiveTab] = useState('description');
  const [relatedProducts, setRelatedProducts] = useState([]);
  const [relatedLoading, setRelatedLoading] = useState(true);

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

    // Fetch related products
    const fetchRelated = async () => {
      try {
        setRelatedLoading(true);
        const response = await api.get(`/storefront/products/${slug}/related`);
        setRelatedProducts(response || []);
      } catch (err) {
        console.error('Failed to load related products:', err);
        setRelatedProducts([]);
      } finally {
        setRelatedLoading(false);
      }
    };
    fetchRelated();
  }, [slug]);

  const variants = product?.variants || [];
  const images = product?.images?.length > 0 ? product.images : [];

  const hasVariants = variants.length > 0;

  const colorAttrName = useMemo(() => {
    if (!variants.length) return null;
    const first = variants[0];
    if (first.attributes) {
      const colorAttr = first.attributes.find((a) => a.is_color);
      if (colorAttr) return colorAttr.attribute.toLowerCase();
    }
    return 'color';
  }, [variants]);

  const sizeAttrName = useMemo(() => {
    if (!variants.length) return 'size';
    const first = variants[0];
    if (first.attributes) {
      const colorAttr = first.attributes.find((a) => a.is_color);
      const sizeAttr = first.attributes.find((a) => a !== colorAttr && !a.is_color);
      if (sizeAttr) return sizeAttr.attribute.toLowerCase();
    }
    return 'size';
  }, [variants]);

  const uniqueColors = useMemo(() => {
    if (!variants.length) return [];
    const attr = colorAttrName || 'color';
    return [...new Set(variants.map((v) => v[attr]).filter(Boolean))];
  }, [variants, colorAttrName]);

  const uniqueSizes = useMemo(() => {
    if (!variants.length) return [];
    const attr = sizeAttrName || 'size';
    return [...new Set(variants.map((v) => v[attr]).filter(Boolean))];
  }, [variants, sizeAttrName]);

  const colorVariants = useMemo(() => {
    if (!variants.length) return [];
    const attr = colorAttrName || 'color';
    if (!selectedSize) return [...new Map(variants.map((v) => [v[attr], v])).values()];
    return variants.filter((v) => v[sizeAttrName] === selectedSize).reduce((acc, v) => {
      if (!acc.find((a) => a[attr] === v[attr])) acc.push(v);
      return acc;
    }, []);
  }, [variants, selectedSize, colorAttrName, sizeAttrName]);

  const sizeVariants = useMemo(() => {
    if (!variants.length) return [];
    const attr = colorAttrName || 'color';
    return variants.filter((v) => v[attr] === selectedColor).reduce((acc, v) => {
      if (!acc.find((a) => a[sizeAttrName] === v[sizeAttrName])) acc.push(v);
      return acc;
    }, []);
  }, [variants, selectedColor, colorAttrName, sizeAttrName]);

  const activeVariant = useMemo(() => {
    if (!variants.length) return null;
    const colorAttr = colorAttrName || 'color';
    const sizeAttr = sizeAttrName || 'size';
    if (selectedColor && selectedSize) {
      return variants.find((v) => v[colorAttr] === selectedColor && v[sizeAttr] === selectedSize) || null;
    }
    if (selectedColor) {
      return variants.find((v) => v[colorAttr] === selectedColor) || null;
    }
    if (selectedSize) {
      return variants.find((v) => v[sizeAttr] === selectedSize) || null;
    }
    return null;
  }, [variants, selectedColor, selectedSize, colorAttrName, sizeAttrName]);

  const variantSelectionComplete = useMemo(() => {
    if (!hasVariants) return true;
    const needsColor = uniqueColors.length > 0;
    const needsSize = uniqueSizes.length > 0;
    if (needsColor && needsSize) return !!selectedColor && !!selectedSize;
    if (needsColor) return !!selectedColor;
    if (needsSize) return !!selectedSize;
    return true;
  }, [hasVariants, uniqueColors, uniqueSizes, selectedColor, selectedSize]);

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
      <div className="pt-14">
        <div className="container mx-auto px-4 py-4">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <div>
              <div className="aspect-square bg-gray-100 rounded-xl animate-pulse mb-4" />
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
      <div className="pt-14">
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
      <div className="pt-14">
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
  }    return (
    <div className="pt-14">
      <div className="container mx-auto px-4 py-4">
        <nav className="flex items-center gap-2 text-xs text-gray-400 mb-2 uppercase tracking-wider">
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
            <ImageZoom
              src={images[currentImageIndex] || null}
              alt={product.name}
              images={images}
              currentIndex={currentImageIndex}
              onImageChange={setSelectedImage}
            />
            {images.length > 1 && (
              <div className="flex gap-3 mt-4">
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

            {/* Description & Reviews Tabs */}
            <div className="mt-8 border border-gray-200 rounded-xl overflow-hidden">
              {/* Tab Buttons */}
              <div className="flex border-b border-gray-200 bg-gray-50/50">
                <button
                  onClick={() => setActiveTab('description')}
                  className={`flex-1 flex items-center justify-center gap-2 px-4 py-3.5 text-xs font-bold uppercase tracking-wider transition-all duration-200 relative ${
                    activeTab === 'description'
                      ? 'text-gray-900 bg-white'
                      : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50'
                  }`}
                >
                  <MessageSquare className="w-3.5 h-3.5" />
                  Description
                  {activeTab === 'description' && (
                    <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-900" />
                  )}
                </button>
                <button
                  onClick={() => setActiveTab('reviews')}
                  className={`flex-1 flex items-center justify-center gap-2 px-4 py-3.5 text-xs font-bold uppercase tracking-wider transition-all duration-200 relative ${
                    activeTab === 'reviews'
                      ? 'text-gray-900 bg-white'
                      : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50'
                  }`}
                >
                  <Star className="w-3.5 h-3.5" />
                  Reviews
                  {activeTab === 'reviews' && (
                    <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-900" />
                  )}
                </button>
              </div>

              {/* Tab Content */}
              <div className="p-6">
                {activeTab === 'description' && (
                  <div className="animate-fade-in">
                    {product.description ? (
                      <div>
                        <p className="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{product.description}</p>

                        {product.attributes?.length > 0 && (
                          <div className="mt-6 pt-6 border-t border-gray-100">
                            <h4 className="text-xs font-bold uppercase tracking-wider text-gray-900 mb-4">Product Details</h4>
                            <dl className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                              {product.attributes.map((attr, i) => (
                                <div key={i} className="flex items-center gap-3 bg-gray-50 rounded-lg px-4 py-3">
                                  <Ruler className="w-4 h-4 text-gray-400 flex-shrink-0" />
                                  <div>
                                    <dt className="text-[11px] text-gray-400 uppercase tracking-wider">{attr.attribute}</dt>
                                    <dd className="text-sm text-gray-900 font-medium">{attr.value}</dd>
                                  </div>
                                </div>
                              ))}
                            </dl>
                          </div>
                        )}
                      </div>
                    ) : (
                      <p className="text-sm text-gray-400 text-center py-8">No description available.</p>
                    )}
                  </div>
                )}

                {activeTab === 'reviews' && (
                  <div className="animate-fade-in">
                    <div className="text-center py-8">
                      <div className="w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-4">
                        <Star className="w-7 h-7 text-amber-400" />
                      </div>
                      <h4 className="text-base font-semibold text-gray-900 mb-2">No Reviews Yet</h4>
                      <p className="text-sm text-gray-400 max-w-xs mx-auto mb-6">
                        Be the first to share your thoughts about this product.
                      </p>
                      <button className="inline-flex items-center gap-2 bg-gray-900 text-white px-5 py-2.5 text-xs font-semibold uppercase tracking-wider rounded-lg hover:bg-gray-800 transition">
                        <MessageSquare className="w-3.5 h-3.5" />
                        Write a Review
                      </button>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>

          <div className="flex flex-col">
            {(product.category || product.brand) && (
              <div className="flex items-center gap-3 mb-2">
                {product.category && (
                  <span className="text-xs uppercase tracking-[0.2em] text-gray-400">
                    {product.category.name}
                  </span>
                )}
                {product.brand && (
                  <span className="text-xs font-medium text-gray-900 bg-gray-100 px-2.5 py-0.5 rounded-full">
                    {product.brand.name}
                  </span>
                )}
                {product.sku && (
                  <span className="text-[10px] font-mono text-gray-400 bg-gray-50 px-2 py-0.5 rounded border border-gray-200">
                    SKU: {product.sku}
                  </span>
                )}
              </div>
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
                  {colorAttrName ? colorAttrName.charAt(0).toUpperCase() + colorAttrName.slice(1) : 'Color'}: <span className="text-gray-900">{selectedColor || uniqueColors[0]}</span>
                </h4>
                <div className="flex gap-2">
                  {colorVariants.map((v) => {
                    const attr = colorAttrName || 'color';
                    return (
                      <button
                        key={v[attr]}
                        onClick={() => { setSelectedColor(v[attr]); setSelectedSize(null); }}
                        className={`w-10 h-10 rounded-full border-2 transition-all duration-200 ${
                          selectedColor === v[attr]
                            ? 'border-gray-900 ring-2 ring-gray-900 ring-offset-2 scale-110'
                            : 'border-gray-200 hover:border-gray-400'
                        }`}
                        style={{ backgroundColor: resolveColor(v[attr]) }}
                        title={v[attr]}
                      />
                    );
                  })}
                </div>
              </div>
            )}

            {uniqueSizes.length > 0 && (
              <div className="mb-6">
                <h4 className="text-xs font-bold uppercase tracking-wider mb-3">
                  {sizeAttrName ? sizeAttrName.charAt(0).toUpperCase() + sizeAttrName.slice(1) : 'Size'}: <span className="text-gray-900">{selectedSize || 'Select'}</span>
                </h4>
                <div className="flex gap-2 flex-wrap">
                  {(selectedColor ? sizeVariants : variants).filter((v, i, a) => {
                    const attr = sizeAttrName || 'size';
                    return a.findIndex((x) => x[attr] === v[attr]) === i;
                  }).map((v) => {
                    const attr = sizeAttrName || 'size';
                    return (
                      <button
                        key={v[attr]}
                        onClick={() => setSelectedSize(selectedSize === v[attr] ? null : v[attr])}
                        className={`w-12 h-10 text-xs font-medium rounded-md border transition-all duration-200 ${
                          selectedSize === v[attr]
                            ? 'border-gray-900 bg-gray-900 text-white shadow-sm'
                            : 'border-gray-200 text-gray-600 hover:border-gray-900 hover:text-gray-900'
                        }`}
                      >
                        {v[attr]}
                      </button>
                    );
                  })}
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
                      variant_id: activeVariant?.id,
                      size: selectedSize,
                      color: selectedColor,
                      colorAttrName,
                      sizeAttrName,
                    });
                  }
                }}
                disabled={isOutOfStock || !variantSelectionComplete}
                className="flex-1 flex items-center justify-center gap-2 bg-gray-900 text-white py-3 px-6 text-sm font-medium hover:bg-gray-800 transition uppercase tracking-wider rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <ShoppingCart className="w-4 h-4" />
                {isOutOfStock ? 'Out of Stock' : !variantSelectionComplete ? 'Select Variant' : 'Add to Cart'}
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

            {/* Product Information */}
            {product.sku || product.attributes?.length > 0 ? (
              <div className="border-t border-gray-100 pt-6 mt-2">
                <h4 className="text-xs font-bold uppercase tracking-wider text-gray-900 mb-4">
                  Product Information
                </h4>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                  {product.sku && (
                    <div className="flex items-center gap-2.5 bg-gray-50 rounded-lg px-3.5 py-2.5">
                      <Tag className="w-3.5 h-3.5 text-gray-400 flex-shrink-0" />
                      <div>
                        <span className="text-[10px] text-gray-400 uppercase tracking-wider block">Model / SKU</span>
                        <span className="text-sm text-gray-900 font-medium font-mono">{product.sku}</span>
                      </div>
                    </div>
                  )}
                  {product.category && (
                    <div className="flex items-center gap-2.5 bg-gray-50 rounded-lg px-3.5 py-2.5">
                      <Package className="w-3.5 h-3.5 text-gray-400 flex-shrink-0" />
                      <div>
                        <span className="text-[10px] text-gray-400 uppercase tracking-wider block">Category</span>
                        <span className="text-sm text-gray-900 font-medium">{product.category.name}</span>
                      </div>
                    </div>
                  )}
                  {product.brand && (
                    <div className="flex items-center gap-2.5 bg-gray-50 rounded-lg px-3.5 py-2.5">
                      <ShieldCheck className="w-3.5 h-3.5 text-gray-400 flex-shrink-0" />
                      <div>
                        <span className="text-[10px] text-gray-400 uppercase tracking-wider block">Brand</span>
                        <span className="text-sm text-gray-900 font-medium">{product.brand.name}</span>
                      </div>
                    </div>
                  )}
                  <div className="flex items-center gap-2.5 bg-gray-50 rounded-lg px-3.5 py-2.5">
                    <Package className="w-3.5 h-3.5 text-gray-400 flex-shrink-0" />
                    <div>
                      <span className="text-[10px] text-gray-400 uppercase tracking-wider block">Availability</span>
                      <span className={`text-sm font-medium ${isOutOfStock ? 'text-red-500' : 'text-green-600'}`}>
                        {isOutOfStock ? 'Out of Stock' : `${displayStock} in stock`}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            ) : (
              product.brand && (
                <div className="border-t border-gray-100 pt-6 mt-2">
                  <div className="flex items-center gap-2.5 bg-gray-50 rounded-lg px-3.5 py-2.5">
                    <ShieldCheck className="w-3.5 h-3.5 text-gray-400 flex-shrink-0" />
                    <div>
                      <span className="text-[10px] text-gray-400 uppercase tracking-wider block">Brand</span>
                      <span className="text-sm text-gray-900 font-medium">{product.brand.name}</span>
                    </div>
                  </div>
                </div>
              )
            )}

          </div>
        </div>

        {/* Related Products Section */}
        <div className="mt-12 md:mt-16 border-t border-gray-100 pt-8 md:pt-12">
          <ProductSection
            title="Related Products"
            products={relatedProducts}
            loading={relatedLoading}
            initialCount={4}
          />
        </div>
      </div>
    </div>
  );
}
