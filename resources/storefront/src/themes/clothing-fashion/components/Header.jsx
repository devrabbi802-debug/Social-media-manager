import React, { useState, useEffect, useRef, useMemo } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { Search, ShoppingCart, Menu, X, ChevronDown, User, LayoutDashboard, LogOut, ArrowRight, Camera, X as XIcon } from 'lucide-react';
import { useCart } from '../contexts/CartContext';
import { useAuth } from '../../../contexts/AuthContext';
import api from '../../../api/client';

let searchTimeout;

export default function Header({ storeName, storeLogo }) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [focusedIndex, setFocusedIndex] = useState(-1);
  const [searchImage, setSearchImage] = useState(null);
  const [searchImagePreview, setSearchImagePreview] = useState(null);
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const searchInputRef = useRef(null);
  const searchPanelRef = useRef(null);
  const imageInputRef = useRef(null);
  const userMenuRef = useRef(null);
  const navigate = useNavigate();
  const location = useLocation();
  const { openDrawer, itemCount } = useCart();
  const { user, isAuthenticated, logout } = useAuth();

  const [categories, setCategories] = useState([]);
  const [navLoading, setNavLoading] = useState(true);
  const [searchResults, setSearchResults] = useState([]);
  const [searchLoading, setSearchLoading] = useState(false);

  useEffect(() => {
    let cancelled = false;
    api.get('/storefront/categories').then((data) => {
      if (cancelled) return;
      const arr = Array.isArray(data) ? data : [];
      setCategories(arr);
    }).catch(() => {
      if (!cancelled) setCategories([]);
    }).finally(() => {
      if (!cancelled) setNavLoading(false);
    });
    return () => { cancelled = true; };
  }, []);

  useEffect(() => {
    if (!searchQuery.trim()) {
      setSearchResults([]);
      return;
    }
    if (searchImagePreview) return;
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      setSearchLoading(true);
      api.get('/storefront/products', { params: { search: searchQuery, per_page: 6 } }).then((res) => {
        setSearchResults(res.data || []);
      }).catch(() => {
        setSearchResults([]);
      }).finally(() => {
        setSearchLoading(false);
      });
    }, 300);
    return () => clearTimeout(searchTimeout);
  }, [searchQuery, searchImagePreview]);

  const navItems = useMemo(() => {
    return categories.map((cat) => ({
      label: cat.name,
      link: `/category/${cat.slug}`,
      submenu: (cat.children || []).map((child) => ({
        name: child.name,
        slug: child.slug,
      })),
    }));
  }, [categories]);

  const hasNavItems = navItems.length > 0;

  const suggestions = searchResults;
  const hasSuggestions = suggestions.length > 0;

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
      if (userMenuRef.current && !userMenuRef.current.contains(e.target)) {
        setUserMenuOpen(false);
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

  const headerBg = 'bg-white shadow-md';
  const textColor = 'text-gray-900';
  const submenuBg = 'bg-white';
  const submenuText = 'text-gray-600 hover:text-gray-900';
  const searchInputBg = 'bg-white text-gray-900 border-gray-300';
  const suggestionBg = 'bg-white';
  const suggestionHover = 'hover:bg-gray-50';

  return (
    <header id="store-header" className={`fixed top-8 left-0 right-0 z-50 transition-all duration-300 ${headerBg} ${textColor}`}>
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
            {!navLoading && hasNavItems && navItems.map((item, i) => (
              <div key={i} className="relative group">
                <Link
                  to={item.link}
                  className="flex items-center gap-1 text-sm uppercase tracking-[0.15em] hover:opacity-70 transition font-medium"
                >
                  {item.label}
                  {item.submenu.length > 0 && <ChevronDown className="w-3 h-3" />}
                </Link>
                {item.submenu.length > 0 && (
                  <div className={`absolute top-full left-1/2 -translate-x-1/2 mt-2 min-w-[180px] shadow-xl py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 ${submenuBg}`}>
                    {item.submenu.map((sub, j) => (
                      <Link
                        key={j}
                        to={`/category/${sub.slug}`}
                        className={`block px-5 py-2 text-sm ${submenuText} transition`}
                      >
                        {sub.name}
                      </Link>
                    ))}
                  </div>
                )}
              </div>
            ))}
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

            <div className="relative" ref={userMenuRef}>
              <button
                onClick={() => isAuthenticated ? setUserMenuOpen(!userMenuOpen) : navigate('/auth')}
                className="hidden md:flex p-2 hover:opacity-70 transition items-center gap-1.5"
                title="My Account"
              >
                {isAuthenticated && user ? (
                  <span className="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-bold flex items-center justify-center">
                    {user.name?.charAt(0)?.toUpperCase() || 'U'}
                  </span>
                ) : (
                  <User className="w-5 h-5" />
                )}
              </button>

              {isAuthenticated && userMenuOpen && (
                <div className="absolute right-0 top-full mt-1 w-48 bg-white border border-gray-100 shadow-xl z-50 py-1">
                  <div className="px-4 py-2 border-b border-gray-50">
                    <p className="text-sm font-medium text-gray-900 truncate">{user.name}</p>
                    <p className="text-xs text-gray-400 truncate">{user.email}</p>
                  </div>
                  <Link
                    to="/dashboard"
                    onClick={() => setUserMenuOpen(false)}
                    className="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 transition"
                  >
                    <LayoutDashboard className="w-4 h-4" />
                    Dashboard
                  </Link>
                  <button
                    onClick={() => { setUserMenuOpen(false); logout(); }}
                    className="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition"
                  >
                    <LogOut className="w-4 h-4" />
                    Sign Out
                  </button>
                </div>
              )}
            </div>

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
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
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
                  className="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded transition hover:bg-gray-100 text-gray-400 hover:text-gray-900"
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
              <div className="mt-2 p-3 border rounded flex items-center gap-3 border-gray-200 bg-gray-50">
                <div className="w-12 h-12 rounded overflow-hidden bg-gray-100 flex-shrink-0">
                  <img src={searchImagePreview} alt="" className="w-full h-full object-cover" />
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-xs font-medium text-gray-700">
                    {searchImage?.name || 'Image selected'}
                  </p>
                  <p className="text-[10px] text-gray-400 mt-0.5">
                    Click Search to find similar products
                  </p>
                </div>
                <button
                  type="button"
                  onClick={clearSearchImage}
                  className="p-1 rounded transition hover:bg-gray-200 text-gray-400"
                >
                  <XIcon className="w-3.5 h-3.5" />
                </button>
              </div>
            )}

            {searchQuery.trim() && !searchImagePreview && (
              <div className={`absolute top-full left-0 right-0 mt-0.5 shadow-2xl border border-gray-100 ${suggestionBg} max-h-96 overflow-y-auto`}>
                {searchLoading ? (
                  <div className="px-4 py-8 text-center text-sm text-gray-400">
                    <div className="inline-block w-5 h-5 border-2 border-gray-300 border-t-gray-900 rounded-full animate-spin" />
                  </div>
                ) : hasSuggestions ? (
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
                          focusedIndex === index ? 'bg-gray-50' : ''
                        } ${suggestionHover}`}
                      >
                        <div className="w-10 h-12 bg-gray-100 overflow-hidden flex-shrink-0">
                          <img src={product.image} alt="" className="w-full h-full object-cover" />
                        </div>
                        <div className="flex-1 min-w-0">
                          <p className="text-sm font-medium line-clamp-1 text-gray-900">
                            {highlightMatch(product.name, searchQuery)}
                          </p>
                          <p className="text-xs text-gray-400 mt-0.5">{product.category?.name || product.category || ''}</p>
                        </div>
                        <div className="text-right flex-shrink-0">
                          <p className="text-sm font-bold text-gray-900">৳{product.effective_price}</p>
                          {product.discount_price && (
                            <p className="text-xs text-gray-400 line-through">৳{product.base_price}</p>
                          )}
                        </div>
                      </button>
                    ))}
                    <Link
                      to={`/products?search=${encodeURIComponent(searchQuery)}`}
                      onClick={closeSearch}
                      className="flex items-center justify-center gap-1.5 px-4 py-3 text-sm font-medium border-t border-gray-100 transition text-gray-900 hover:bg-gray-50"
                    >
                      View all results
                      <ArrowRight className="w-4 h-4" />
                    </Link>
                  </>
                ) : (
                  <div className="px-4 py-8 text-center text-sm text-gray-400">
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
              <Link to="/" onClick={() => setMobileMenuOpen(false)} className="py-2.5 text-sm uppercase tracking-widest font-medium">Home</Link>
              {!navLoading && hasNavItems && navItems.map((item, i) => (
                <div key={i}>
                  <Link to={item.link} onClick={() => setMobileMenuOpen(false)} className="py-2.5 text-sm uppercase tracking-widest font-medium block">
                    {item.label}
                  </Link>
                  {item.submenu.length > 0 && (
                    <div className="pl-4 pb-2 space-y-1">
                      {item.submenu.map((sub, j) => (
                        <Link key={j} to={`/category/${sub.slug}`} onClick={() => setMobileMenuOpen(false)} className="block py-1.5 text-xs text-gray-500 hover:text-gray-900 transition">
                          {sub.name}
                        </Link>
                      ))}
                    </div>
                  )}
                </div>
              ))}
              <button onClick={() => { openDrawer(); setMobileMenuOpen(false); }} className="py-2.5 text-sm uppercase tracking-widest font-medium text-left">Cart ({itemCount})</button>
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
