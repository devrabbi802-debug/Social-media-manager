import React, { useState, useEffect, useRef, useMemo } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { Search, ShoppingCart, Menu, X, ChevronDown, User, LayoutDashboard, ArrowRight, Camera, Upload, X as XIcon } from 'lucide-react';
import { useCart } from '../contexts/CartContext';

const menuItems = [
  {
    label: 'Men',
    link: '/products?category=men',
    submenu: ['T-Shirts', 'Shirts', 'Jeans', 'Jackets', 'Hoodies', 'Shoes'],
  },
  {
    label: 'Women',
    link: '/products?category=women',
    submenu: ['Dresses', 'Tops', 'Skirts', 'Jeans', 'Jackets', 'Accessories'],
  },
  {
    label: 'Kids',
    link: '/products?category=kids',
    submenu: ['Boys', 'Girls', 'Infants', 'Toys', 'School'],
  },
  {
    label: 'Collection',
    link: '/products',
    submenu: ['Summer 2026', 'New Arrivals', 'Best Sellers', 'Sale'],
  },
];

const searchProducts = [
  { id: 1, name: 'Classic Cotton T-Shirt', slug: 'classic-cotton-tshirt', base_price: 1200, discount_price: 799, effective_price: 799, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=200&q=80', category: 'T-Shirts' },
  { id: 2, name: 'Slim Fit Denim Jeans', slug: 'slim-fit-denim-jeans', base_price: 2500, discount_price: 1899, effective_price: 1899, image: 'https://images.unsplash.com/photo-1542272454315-4c01d7abdf4a?w=200&q=80', category: 'Denim' },
  { id: 3, name: 'Oversized Hoodie', slug: 'oversized-hoodie', base_price: 1800, discount_price: null, effective_price: 1800, image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=200&q=80', category: 'Hoodies' },
  { id: 4, name: 'Leather Biker Jacket', slug: 'leather-biker-jacket', base_price: 5500, discount_price: 4200, effective_price: 4200, image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=200&q=80', category: 'Jackets' },
  { id: 5, name: 'Running Shoes Pro', slug: 'running-shoes-pro', base_price: 3200, discount_price: 2599, effective_price: 2599, image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200&q=80', category: 'Shoes' },
  { id: 6, name: 'Premium Cap', slug: 'premium-cap', base_price: 600, discount_price: 399, effective_price: 399, image: 'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?w=200&q=80', category: 'Accessories' },
  { id: 7, name: 'Graphic Print T-Shirt', slug: 'graphic-print-tshirt', base_price: 1500, discount_price: null, effective_price: 1500, image: 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=200&q=80', category: 'T-Shirts' },
  { id: 8, name: 'Cargo Pants', slug: 'cargo-pants', base_price: 2200, discount_price: 1799, effective_price: 1799, image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=200&q=80', category: 'Denim' },
  { id: 9, name: 'Wool Blend Hoodie', slug: 'wool-blend-hoodie', base_price: 2400, discount_price: 1999, effective_price: 1999, image: 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=200&q=80', category: 'Hoodies' },
  { id: 10, name: 'Bomber Jacket', slug: 'bomber-jacket', base_price: 4800, discount_price: 3800, effective_price: 3800, image: 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?w=200&q=80', category: 'Jackets' },
  { id: 11, name: 'Summer Linen Shirt', slug: 'summer-linen-shirt', base_price: 1600, discount_price: null, effective_price: 1600, image: 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=200&q=80', category: 'T-Shirts' },
  { id: 12, name: 'Casual Sneakers', slug: 'casual-sneakers', base_price: 2800, discount_price: null, effective_price: 2800, image: 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=200&q=80', category: 'Shoes' },
  { id: 13, name: 'Ripped Skinny Jeans', slug: 'ripped-skinny-jeans', base_price: 2800, discount_price: 2200, effective_price: 2200, image: 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=200&q=80', category: 'Denim' },
  { id: 14, name: 'Denim Jacket', slug: 'denim-jacket', base_price: 4200, discount_price: 3500, effective_price: 3500, image: 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=200&q=80', category: 'Jackets' },
  { id: 15, name: 'Leather Belt', slug: 'leather-belt', base_price: 900, discount_price: 699, effective_price: 699, image: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=200&q=80', category: 'Accessories' },
  { id: 16, name: 'Polo T-Shirt', slug: 'polo-tshirt', base_price: 1400, discount_price: 1099, effective_price: 1099, image: 'https://images.unsplash.com/photo-1598713125249-ba7e3b3c02ba?w=200&q=80', category: 'T-Shirts' },
  { id: 17, name: 'Sport Shoes Elite', slug: 'sport-shoes-elite', base_price: 4500, discount_price: 3600, effective_price: 3600, image: 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=200&q=80', category: 'Shoes' },
  { id: 18, name: 'Winter Beanie', slug: 'winter-beanie', base_price: 500, discount_price: 399, effective_price: 399, image: 'https://images.unsplash.com/photo-1576871337622-98d48d1cf531?w=200&q=80', category: 'Accessories' },
  { id: 19, name: 'Puffer Jacket', slug: 'puffer-jacket', base_price: 6200, discount_price: 4900, effective_price: 4900, image: 'https://images.unsplash.com/photo-1604644401890-0bd678c83788?w=200&q=80', category: 'Jackets' },
  { id: 20, name: 'Chino Pants', slug: 'chino-pants', base_price: 2000, discount_price: null, effective_price: 2000, image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=200&q=80', category: 'Denim' },
];

export default function Header({ storeName, storeLogo }) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [focusedIndex, setFocusedIndex] = useState(-1);
  const [scrolled, setScrolled] = useState(false);
  const [searchImage, setSearchImage] = useState(null);
  const [searchImagePreview, setSearchImagePreview] = useState(null);
  const searchInputRef = useRef(null);
  const searchPanelRef = useRef(null);
  const imageInputRef = useRef(null);
  const navigate = useNavigate();
  const location = useLocation();
  const { openDrawer, itemCount } = useCart();
  const isHome = location.pathname === '/';

  const suggestions = useMemo(() => {
    if (!searchQuery.trim()) return [];
    const q = searchQuery.toLowerCase();
    return searchProducts
      .filter((p) => p.name.toLowerCase().includes(q) || p.category.toLowerCase().includes(q))
      .slice(0, 6);
  }, [searchQuery]);

  const hasSuggestions = suggestions.length > 0;

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 60);
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => {
    setMobileMenuOpen(false);
    setSearchOpen(false);
    setSearchQuery('');
    setFocusedIndex(-1);
  }, [location.pathname]);

  useEffect(() => {
    if (searchOpen && searchInputRef.current) {
      searchInputRef.current.focus();
    }
  }, [searchOpen]);

  useEffect(() => {
    const handleClickOutside = (e) => {
      if (searchPanelRef.current && !searchPanelRef.current.contains(e.target) && !e.target.closest('[data-search-toggle]')) {
        setSearchOpen(false);
        setSearchQuery('');
        setFocusedIndex(-1);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleSearch = (e) => {
    e.preventDefault();
    if (searchImage) {
      navigate('/products?image_search=1');
      closeSearch();
    } else if (searchQuery.trim()) {
      navigate(`/products?search=${encodeURIComponent(searchQuery)}`);
      closeSearch();
    }
  };

  const closeSearch = () => {
    setSearchOpen(false);
    setSearchQuery('');
    setSearchImage(null);
    setSearchImagePreview(null);
    setFocusedIndex(-1);
  };

  const handleImageSelect = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    setSearchImage(file);
    setSearchQuery('');
    setFocusedIndex(-1);
    const reader = new FileReader();
    reader.onload = (ev) => setSearchImagePreview(ev.target.result);
    reader.readAsDataURL(file);
  };

  const clearSearchImage = () => {
    setSearchImage(null);
    setSearchImagePreview(null);
    if (imageInputRef.current) imageInputRef.current.value = '';
  };

  const goToProduct = (slug) => {
    navigate(`/products/${slug}`);
    closeSearch();
  };

  const handleKeyDown = (e) => {
    if (!hasSuggestions) return;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setFocusedIndex((prev) => (prev < suggestions.length - 1 ? prev + 1 : 0));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setFocusedIndex((prev) => (prev > 0 ? prev - 1 : suggestions.length - 1));
    } else if (e.key === 'Enter' && focusedIndex >= 0) {
      e.preventDefault();
      goToProduct(suggestions[focusedIndex].slug);
    }
  };

  const headerBg = isHome && !scrolled ? 'bg-transparent' : 'bg-white shadow-md';
  const textColor = isHome && !scrolled ? 'text-white' : 'text-gray-900';
  const submenuBg = isHome && !scrolled ? 'bg-gray-900/95' : 'bg-white';
  const submenuText = isHome && !scrolled ? 'text-gray-300 hover:text-white' : 'text-gray-600 hover:text-gray-900';
  const searchInputBg = isHome && !scrolled ? 'bg-white/10 text-white placeholder-white/50 border-white/20' : 'bg-white text-gray-900 border-gray-300';
  const suggestionBg = isHome && !scrolled ? 'bg-gray-900/95' : 'bg-white';
  const suggestionHover = isHome && !scrolled ? 'hover:bg-white/10' : 'hover:bg-gray-50';

  return (
    <header className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${headerBg} ${textColor}`}>
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between h-16">
          <div className="flex items-center">
            <Link to="/" className="flex items-center space-x-2">
              {storeLogo ? (
                <img src={storeLogo} alt={storeName} className="h-8 w-auto object-contain" />
              ) : (
                <span className="text-xl font-bold tracking-tight">FASHION</span>
              )}
            </Link>
          </div>

          <nav className="hidden lg:flex items-center space-x-10">
            <Link to="/" className="text-sm uppercase tracking-[0.15em] hover:opacity-70 transition font-medium">
              Home
            </Link>
            {menuItems.map((item, i) => (
              <div key={i} className="relative group">
                <Link
                  to={item.link}
                  className="flex items-center gap-1 text-sm uppercase tracking-[0.15em] hover:opacity-70 transition font-medium"
                >
                  {item.label}
                  <ChevronDown className="w-3 h-3" />
                </Link>
                <div className={`absolute top-full left-1/2 -translate-x-1/2 mt-2 min-w-[180px] shadow-xl py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 ${submenuBg}`}>
                  {item.submenu.map((sub, j) => (
                    <Link
                      key={j}
                      to={`/products?category=${sub.toLowerCase().replace(/\s+/g, '-')}`}
                      className={`block px-5 py-2 text-sm ${submenuText} transition`}
                    >
                      {sub}
                    </Link>
                  ))}
                </div>
              </div>
            ))}
            <Link to="/products" className="text-sm uppercase tracking-[0.15em] hover:opacity-70 transition font-medium">
              Shop All
            </Link>
          </nav>

          <div className="flex items-center space-x-3">
            <button
              data-search-toggle
              onClick={() => setSearchOpen(!searchOpen)}
              className="p-2 hover:opacity-70 transition"
            >
              <Search className="w-5 h-5" />
            </button>

            <button onClick={openDrawer} className="p-2 hover:opacity-70 transition relative">
              <ShoppingCart className="w-5 h-5" />
              <span className="absolute -top-1 -right-1 bg-gray-900 text-white text-[10px] w-4 h-4 flex items-center justify-center">
                {itemCount}
              </span>
            </button>

            <Link to="/auth" className="hidden md:block p-2 hover:opacity-70 transition" title="My Account">
              <User className="w-5 h-5" />
            </Link>

            <Link to="/dashboard" className="hidden lg:block p-2 hover:opacity-70 transition" title="Dashboard">
              <LayoutDashboard className="w-5 h-5" />
            </Link>

            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="lg:hidden p-2 hover:opacity-70 transition"
            >
              {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </button>
          </div>
        </div>

        {searchOpen && (
          <div ref={searchPanelRef} className="relative pb-3">
            <form onSubmit={handleSearch} className="flex">
              <div className="relative flex-1">
                <Search className={`absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 ${isHome && !scrolled ? 'text-white/50' : 'text-gray-400'}`} />
                <input
                  ref={searchInputRef}
                  type="text"
                  value={searchQuery}
                  onChange={(e) => { setSearchQuery(e.target.value); setFocusedIndex(-1); clearSearchImage(); }}
                  onKeyDown={handleKeyDown}
                  placeholder="Search products..."
                  className={`w-full pl-10 pr-16 py-2.5 text-sm border focus:outline-none focus:border-gray-900 transition ${searchInputBg}`}
                />
                <button
                  type="button"
                  onClick={() => imageInputRef.current?.click()}
                  className={`absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded transition ${
                    isHome && !scrolled
                      ? 'hover:bg-white/10 text-white/60 hover:text-white'
                      : 'hover:bg-gray-100 text-gray-400 hover:text-gray-900'
                  }`}
                  title="Search by image"
                >
                  <Camera className="w-4 h-4" />
                </button>
                <input
                  ref={imageInputRef}
                  type="file"
                  accept="image/*"
                  onChange={handleImageSelect}
                  className="hidden"
                />
              </div>
              <button type="submit" className="bg-gray-900 text-white px-6 py-2.5 hover:bg-gray-800 transition text-sm uppercase tracking-widest flex items-center gap-1.5">
                <Search className="w-3.5 h-3.5" />
                Search
              </button>
            </form>

            {searchImagePreview && (
              <div className={`mt-2 p-3 border rounded flex items-center gap-3 ${isHome && !scrolled ? 'border-white/20 bg-white/5' : 'border-gray-200 bg-gray-50'}`}>
                <div className="w-12 h-12 rounded overflow-hidden bg-gray-100 flex-shrink-0">
                  <img src={searchImagePreview} alt="" className="w-full h-full object-cover" />
                </div>
                <div className="flex-1 min-w-0">
                  <p className={`text-xs font-medium ${isHome && !scrolled ? 'text-white' : 'text-gray-700'}`}>
                    {searchImage?.name || 'Image selected'}
                  </p>
                  <p className="text-[10px] text-gray-400 mt-0.5">
                    Click Search to find similar products
                  </p>
                </div>
                <button
                  type="button"
                  onClick={clearSearchImage}
                  className={`p-1 rounded transition ${isHome && !scrolled ? 'hover:bg-white/10 text-white/60' : 'hover:bg-gray-200 text-gray-400'}`}
                >
                  <XIcon className="w-3.5 h-3.5" />
                </button>
              </div>
            )}

            {searchQuery.trim() && !searchImagePreview && (
              <div className={`absolute top-full left-0 right-0 mt-0.5 shadow-2xl border ${isHome && !scrolled ? 'border-white/10' : 'border-gray-100'} ${suggestionBg} max-h-96 overflow-y-auto`}>
                {hasSuggestions ? (
                  <>
                    <div className="px-4 py-2 border-b border-gray-100">
                      <span className="text-xs font-medium uppercase tracking-wider text-gray-400">Products</span>
                    </div>
                    {suggestions.map((product, index) => (
                      <button
                        key={product.id}
                        onClick={() => goToProduct(product.slug)}
                        onMouseEnter={() => setFocusedIndex(index)}
                        className={`w-full flex items-center gap-3 px-4 py-2.5 text-left transition ${
                          focusedIndex === index ? (isHome && !scrolled ? 'bg-white/10' : 'bg-gray-50') : ''
                        } ${suggestionHover}`}
                      >
                        <div className="w-10 h-12 bg-gray-100 overflow-hidden flex-shrink-0">
                          <img src={product.image} alt="" className="w-full h-full object-cover" />
                        </div>
                        <div className="flex-1 min-w-0">
                          <p className={`text-sm font-medium line-clamp-1 ${isHome && !scrolled ? 'text-white' : 'text-gray-900'}`}>
                            {highlightMatch(product.name, searchQuery)}
                          </p>
                          <p className="text-xs text-gray-400 mt-0.5">{product.category}</p>
                        </div>
                        <div className="text-right flex-shrink-0">
                          <p className={`text-sm font-bold ${isHome && !scrolled ? 'text-white' : 'text-gray-900'}`}>৳{product.effective_price}</p>
                          {product.discount_price && (
                            <p className="text-xs text-gray-400 line-through">৳{product.base_price}</p>
                          )}
                        </div>
                      </button>
                    ))}
                    <Link
                      to={`/products?search=${encodeURIComponent(searchQuery)}`}
                      onClick={closeSearch}
                      className={`flex items-center justify-center gap-1.5 px-4 py-3 text-sm font-medium border-t border-gray-100 transition ${
                        isHome && !scrolled
                          ? 'text-white hover:bg-white/10'
                          : 'text-gray-900 hover:bg-gray-50'
                      }`}
                    >
                      View all results
                      <ArrowRight className="w-4 h-4" />
                    </Link>
                  </>
                ) : (
                  <div className={`px-4 py-8 text-center text-sm ${isHome && !scrolled ? 'text-white/60' : 'text-gray-400'}`}>
                    No products found for "{searchQuery}"
                  </div>
                )}
              </div>
            )}
          </div>
        )}

        {mobileMenuOpen && (
          <div className="lg:hidden py-4 border-t border-gray-200 max-h-[80vh] overflow-y-auto bg-white text-gray-900 shadow-lg">
            <nav className="flex flex-col space-y-1">
              <Link to="/" className="py-2.5 text-sm uppercase tracking-widest font-medium">Home</Link>
              {menuItems.map((item, i) => (
                <div key={i}>
                  <Link to={item.link} className="py-2.5 text-sm uppercase tracking-widest font-medium block">
                    {item.label}
                  </Link>
                  <div className="pl-4 pb-2 space-y-1">
                    {item.submenu.map((sub, j) => (
                      <Link key={j} to={`/products?category=${sub.toLowerCase().replace(/\s+/g, '-')}`} className="block py-1.5 text-xs text-gray-500 hover:text-gray-900 transition">
                        {sub}
                      </Link>
                    ))}
                  </div>
                </div>
              ))}
              <Link to="/products" className="py-2.5 text-sm uppercase tracking-widest font-medium">Shop All</Link>
              <button onClick={openDrawer} className="py-2.5 text-sm uppercase tracking-widest font-medium text-left">Cart ({itemCount})</button>
            </nav>
          </div>
        )}
      </div>
    </header>
  );
}

function highlightMatch(text, query) {
  if (!query.trim()) return text;
  const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
  const parts = text.split(regex);
  return parts.map((part, i) =>
    regex.test(part) ? <span key={i} className="font-semibold underline decoration-2 underline-offset-2">{part}</span> : part
  );
}
