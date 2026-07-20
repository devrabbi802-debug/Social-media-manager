import React, { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { ShoppingCart, ChevronRight, Star, Minus, Plus, Heart } from 'lucide-react';
import { useCart } from '../contexts/CartContext';

const dummyProduct = {
  id: 1,
  name: 'Premium Cotton Oversized T-Shirt',
  slug: 'premium-cotton-oversized-tshirt',
  base_price: 1800,
  discount_price: 1299,
  effective_price: 1299,
  stock_quantity: 45,
  description: 'Made from 100% organic cotton with a relaxed oversized fit. Features reinforced stitching, a ribbed crew neckline, and a classic silhouette that never goes out of style. Perfect for casual everyday wear.',
  images: [
    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&q=80',
    'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=800&q=80',
    'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=800&q=80',
  ],
  category: { name: 'T-Shirts', slug: 't-shirts' },
  brand: { name: 'Nike', slug: 'nike' },
  colors: ['Black', 'White', 'Gray', 'Navy'],
  sizes: ['S', 'M', 'L', 'XL', 'XXL'],
  attributes: [
    { attribute: 'Material', value: '100% Organic Cotton' },
    { attribute: 'Fit', value: 'Oversized' },
    { attribute: 'Neck', value: 'Crew Neck' },
    { attribute: 'Care', value: 'Machine Wash' },
  ],
};

export default function ProductDetail() {
  const { slug } = useParams();
  const [selectedImage, setSelectedImage] = useState(0);
  const [selectedColor, setSelectedColor] = useState('Black');
  const [selectedSize, setSelectedSize] = useState('M');
  const [quantity, setQuantity] = useState(1);
  const { addToCart } = useCart();

  const product = dummyProduct;
  const hasDiscount = product.discount_price && product.discount_price < product.base_price;

  return (
    <div className="pt-20">
      <div className="container mx-auto px-4 py-8">
        <nav className="flex items-center gap-2 text-xs text-gray-400 mb-8 uppercase tracking-wider">
          <Link to="/" className="hover:text-gray-900 transition">Home</Link>
          <ChevronRight className="w-3 h-3" />
          <Link to="/products" className="hover:text-gray-900 transition">Products</Link>
          <ChevronRight className="w-3 h-3" />
          <span className="text-gray-900">{product.name}</span>
        </nav>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
          <div>
            <div className="aspect-[4/5] bg-gray-50 overflow-hidden mb-4">
              <img
                src={product.images[selectedImage]}
                alt={product.name}
                className="w-full h-full object-cover"
              />
            </div>
            <div className="flex gap-3">
              {product.images.map((img, i) => (
                <button
                  key={i}
                  onClick={() => setSelectedImage(i)}
                  className={`w-20 h-20 bg-gray-50 overflow-hidden border-2 transition ${
                    selectedImage === i ? 'border-gray-900' : 'border-transparent'
                  }`}
                >
                  <img src={img} alt="" className="w-full h-full object-cover" />
                </button>
              ))}
            </div>
          </div>

          <div className="flex flex-col">
            <span className="text-xs uppercase tracking-[0.2em] text-gray-400 mb-2">
              {product.category.name}
            </span>
            <h1 className="text-2xl md:text-3xl font-bold text-gray-900 mb-3">{product.name}</h1>

            <div className="flex items-center gap-2 mb-4">
              <div className="flex items-center gap-0.5">
                {[...Array(5)].map((_, i) => (
                  <Star key={i} className="w-4 h-4 fill-amber-400 text-amber-400" />
                ))}
              </div>
              <span className="text-xs text-gray-400">(24 reviews)</span>
            </div>

            <div className="flex items-baseline gap-3 mb-6">
              <span className="text-3xl font-bold text-gray-900">৳{product.effective_price}</span>
              {hasDiscount && (
                <>
                  <span className="text-lg text-gray-300 line-through">৳{product.base_price}</span>
                  <span className="bg-red-50 text-red-500 text-xs px-2 py-0.5 font-medium">
                    -{Math.round((1 - product.discount_price / product.base_price) * 100)}%
                  </span>
                </>
              )}
            </div>

            <p className="text-xs text-green-600 font-medium mb-6 flex items-center gap-1.5">
              <span className="w-1.5 h-1.5 bg-green-600 rounded-full" />
              In Stock ({product.stock_quantity} available)
            </p>

            <div className="mb-6">
              <h4 className="text-xs font-bold uppercase tracking-wider mb-3">Color: {selectedColor}</h4>
              <div className="flex gap-2">
                {product.colors.map((color) => (
                  <button
                    key={color}
                    onClick={() => setSelectedColor(color)}
                    className={`px-5 py-2 text-xs border transition ${
                      selectedColor === color
                        ? 'border-gray-900 bg-gray-900 text-white'
                        : 'border-gray-200 text-gray-600 hover:border-gray-400'
                    }`}
                  >
                    {color}
                  </button>
                ))}
              </div>
            </div>

            <div className="mb-6">
              <h4 className="text-xs font-bold uppercase tracking-wider mb-3">Size: {selectedSize}</h4>
              <div className="flex gap-2">
                {product.sizes.map((size) => (
                  <button
                    key={size}
                    onClick={() => setSelectedSize(size)}
                    className={`w-12 h-10 text-xs border transition ${
                      selectedSize === size
                        ? 'border-gray-900 bg-gray-900 text-white'
                        : 'border-gray-200 text-gray-600 hover:border-gray-400'
                    }`}
                  >
                    {size}
                  </button>
                ))}
              </div>
            </div>

            <div className="mb-8">
              <h4 className="text-xs font-bold uppercase tracking-wider mb-3">Quantity</h4>
              <div className="flex items-center border border-gray-200 w-fit">
                <button
                  onClick={() => setQuantity(Math.max(1, quantity - 1))}
                  className="w-10 h-10 flex items-center justify-center hover:bg-gray-50 transition"
                >
                  <Minus className="w-3 h-3" />
                </button>
                <span className="w-12 h-10 flex items-center justify-center text-sm font-medium border-x border-gray-200">
                  {quantity}
                </span>
                <button
                  onClick={() => setQuantity(quantity + 1)}
                  className="w-10 h-10 flex items-center justify-center hover:bg-gray-50 transition"
                >
                  <Plus className="w-3 h-3" />
                </button>
              </div>
            </div>

            <div className="flex gap-3 mb-8">
              <button
                onClick={() => addToCart({ ...product, image: product.images[0], price: product.effective_price, size: selectedSize, color: selectedColor })}
                className="flex-1 flex items-center justify-center gap-2 bg-gray-900 text-white py-3 px-6 text-sm font-medium hover:bg-gray-800 transition uppercase tracking-wider"
              >
                <ShoppingCart className="w-4 h-4" />
                Add to Cart
              </button>
              <button className="w-12 h-12 border border-gray-200 flex items-center justify-center hover:border-gray-900 transition">
                <Heart className="w-4 h-4" />
              </button>
            </div>

            <div className="border-t border-gray-100 pt-6">
              <h4 className="text-xs font-bold uppercase tracking-wider mb-3">Description</h4>
              <p className="text-sm text-gray-500 leading-relaxed">{product.description}</p>
            </div>

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
          </div>
        </div>
      </div>
    </div>
  );
}
