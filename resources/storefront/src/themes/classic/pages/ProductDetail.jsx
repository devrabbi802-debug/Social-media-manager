import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { ShoppingCart, ChevronRight } from 'lucide-react';
import ImageZoom from '../../../components/shared/ImageZoom';
import api from '../../../api/client';

export default function ProductDetail() {
  const { slug } = useParams();
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedImage, setSelectedImage] = useState(0);
  const [selectedVariant, setSelectedVariant] = useState(null);

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        setLoading(true);
        const response = await api.get(`/storefront/products/${slug}`);
        setProduct(response?.data || response);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };
    fetchProduct();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }, [slug]);

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div className="aspect-square bg-gray-100 animate-pulse" />
          <div className="space-y-4">
            <div className="h-4 bg-gray-200 rounded w-1/3 animate-pulse" />
            <div className="h-8 bg-gray-200 rounded w-2/3 animate-pulse" />
            <div className="h-6 bg-gray-200 rounded w-1/4 animate-pulse" />
            <div className="h-20 bg-gray-200 rounded animate-pulse" />
          </div>
        </div>
      </div>
    );
  }

  if (error || !product) {
    return (
      <div className="container mx-auto px-4 py-12 text-center">
        <p className="text-red-500">Product not found.</p>
        <Link to="/products" className="text-primary mt-4 inline-block hover:underline">← Back to Products</Link>
      </div>
    );
  }

  const images = product.images?.length > 0 ? product.images : [];
  const currentPrice = product.discount_price || product.base_price;
  const hasDiscount = product.discount_price && product.discount_price < product.base_price;

  return (
    <div className="container mx-auto px-4 py-8">
      <nav className="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <Link to="/" className="hover:text-primary transition">Home</Link>
        <ChevronRight className="w-4 h-4" />
        <Link to="/products" className="hover:text-primary transition">Products</Link>
        {product.category && (
          <>
            <ChevronRight className="w-4 h-4" />
            <Link to={`/category/${product.category.slug}`} className="hover:text-primary transition">{product.category.name}</Link>
          </>
        )}
        <ChevronRight className="w-4 h-4" />
        <span className="text-gray-900">{product.name}</span>
      </nav>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
          <ImageZoom
            src={images.length > 0 ? `/storage/${images[selectedImage]}` : null}
            alt={product.name}
            images={images.map(i => `/storage/${i}`)}
            currentIndex={selectedImage}
            onImageChange={setSelectedImage}
          />
          {images.length > 1 && (
            <div className="flex gap-2 mt-4">
              {images.map((img, i) => (
                <button
                  key={i}
                  onClick={() => setSelectedImage(i)}
                  className={`w-16 h-16 border overflow-hidden transition ${selectedImage === i ? 'border-primary' : 'border-gray-200'}`}
                >
                  <img src={`/storage/${img}`} alt="" className="w-full h-full object-cover" />
                </button>
              ))}
            </div>
          )}
        </div>

        <div>
          {product.category && (
            <Link to={`/category/${product.category.slug}`} className="text-sm text-primary hover:underline">{product.category.name}</Link>
          )}
          <h1 className="text-2xl md:text-3xl font-bold mt-2 mb-4 text-gray-900">{product.name}</h1>
          {product.brand && (
            <p className="text-sm text-gray-500 mb-4">Brand: <span className="text-gray-900">{product.brand.name}</span></p>
          )}

          <div className="flex items-center gap-3 mb-6">
            <span className="text-3xl font-bold text-primary">৳{currentPrice}</span>
            {hasDiscount && (
              <>
                <span className="text-lg text-gray-400 line-through">৳{product.base_price}</span>
                <span className="bg-red-100 text-red-600 text-sm px-2 py-1 font-medium">
                  -{Math.round((1 - product.discount_price / product.base_price) * 100)}%
                </span>
              </>
            )}
          </div>

          <p className={`text-sm mb-6 ${product.stock_quantity > 0 ? 'text-green-600' : 'text-red-500'}`}>
            {product.stock_quantity > 0 ? `In Stock (${product.stock_quantity} available)` : 'Out of Stock'}
          </p>

          {product.variants && product.variants.length > 0 && (
            <div className="mb-6">
              <h3 className="font-medium mb-3">Select Variant</h3>
              <div className="flex flex-wrap gap-2">
                {product.variants.map((variant) => (
                  <button
                    key={variant.id}
                    onClick={() => setSelectedVariant(variant)}
                    className={`px-4 py-2 border transition ${
                      selectedVariant?.id === variant.id
                        ? 'border-primary bg-primary text-white'
                        : 'border-gray-300 hover:border-primary'
                    }`}
                  >
                    {variant.name}
                  </button>
                ))}
              </div>
            </div>
          )}

          {product.description && (
            <div className="mb-6">
              <h3 className="font-medium mb-2">Description</h3>
              <p className="text-gray-600 text-sm leading-relaxed">{product.description}</p>
            </div>
          )}

          {product.attributes && product.attributes.length > 0 && (
            <div className="mb-6">
              <h3 className="font-medium mb-2">Details</h3>
              <dl className="text-sm">
                {product.attributes.map((attr, i) => (
                  <div key={i} className="flex gap-2">
                    <dt className="text-gray-500 w-32">{attr.attribute}:</dt>
                    <dd className="text-gray-900">{attr.value}</dd>
                  </div>
                ))}
              </dl>
            </div>
          )}

          <button
            className="w-full md:w-auto flex items-center justify-center gap-2 bg-primary text-white px-8 py-3 hover:bg-primary/90 transition font-medium"
            disabled={product.stock_quantity === 0}
          >
            <ShoppingCart className="w-5 h-5" />
            Add to Cart
          </button>
        </div>
      </div>
    </div>
  );
}
