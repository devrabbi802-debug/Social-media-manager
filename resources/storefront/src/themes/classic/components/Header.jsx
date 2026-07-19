import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Search, ShoppingCart, Menu, X, ChevronDown } from 'lucide-react';

export default function Header({ storeName, storeLogo, categories = [] }) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const navigate = useNavigate();

  const handleSearch = (e) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      navigate(`/products?search=${encodeURIComponent(searchQuery)}`);
      setSearchOpen(false);
    }
  };

  return (
    <header className="bg-headerBg sticky top-0 z-50 border-b" style={{ color: 'var(--color-header_text, #111827)', borderColor: 'var(--color-border, #D1D5DB)' }}>
      {/* Top bar */}
      <div className="border-b" style={{ borderColor: 'var(--color-border, #D1D5DB)' }}>
        <div className="container mx-auto px-4 py-1 flex justify-between items-center text-xs">
          <span className="text-gray-500">Welcome to our store</span>
          <div className="flex items-center space-x-4">
            <Link to="/products" className="hover:text-primary transition">All Products</Link>
          </div>
        </div>
      </div>

      {/* Main header - centered logo */}
      <div className="container mx-auto px-4 py-4">
        <div className="flex items-center justify-between">
          {/* Left nav */}
          <nav className="hidden md:flex items-center space-x-6">
            <Link to="/" className="font-medium hover:text-primary transition">Home</Link>
            <div className="relative group">
              <button className="flex items-center space-x-1 font-medium hover:text-primary transition">
                <span>Categories</span>
                <ChevronDown className="w-4 h-4" />
              </button>
              {categories.length > 0 && (
                <div className="absolute top-full left-0 mt-2 w-48 bg-white text-gray-900 border border-gray-200 py-2 hidden group-hover:block z-50">
                  {categories.map((cat) => (
                    <Link key={cat.id} to={`/category/${cat.slug}`} className="block px-4 py-2 hover:bg-gray-50 transition">
                      {cat.name}
                    </Link>
                  ))}
                </div>
              )}
            </div>
          </nav>

          {/* Centered logo */}
          <Link to="/" className="absolute left-1/2 -translate-x-1/2">
            {storeLogo ? (
              <img src={storeLogo} alt={storeName} className="h-10 w-auto object-contain" />
            ) : (
              <span className="text-2xl font-bold">{storeName || 'Store'}</span>
            )}
          </Link>

          {/* Right icons */}
          <div className="flex items-center space-x-3">
            <button onClick={() => setSearchOpen(!searchOpen)} className="p-2 hover:bg-gray-100 rounded transition">
              <Search className="w-5 h-5" />
            </button>
            <Link to="/cart" className="p-2 hover:bg-gray-100 rounded transition relative">
              <ShoppingCart className="w-5 h-5" />
              <span className="absolute -top-1 -right-1 bg-primary text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">0</span>
            </Link>
            <button onClick={() => setMobileMenuOpen(!mobileMenuOpen)} className="md:hidden p-2 hover:bg-gray-100 rounded transition">
              {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </button>
          </div>
        </div>
      </div>

      {/* Search bar */}
      {searchOpen && (
        <div className="border-t" style={{ borderColor: 'var(--color-border, #D1D5DB)' }}>
          <div className="container mx-auto px-4 py-3">
            <form onSubmit={handleSearch} className="flex">
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search products..."
                className="flex-1 px-4 py-2 border border-gray-300 focus:outline-none focus:border-primary"
              />
              <button type="submit" className="bg-primary text-white px-6 py-2 hover:bg-primary/90 transition">Search</button>
            </form>
          </div>
        </div>
      )}

      {/* Mobile menu */}
      {mobileMenuOpen && (
        <div className="md:hidden border-t" style={{ borderColor: 'var(--color-border, #D1D5DB)' }}>
          <div className="container mx-auto px-4 py-4">
            <nav className="flex flex-col space-y-3">
              <Link to="/" className="py-2 font-medium hover:text-primary transition" onClick={() => setMobileMenuOpen(false)}>Home</Link>
              <Link to="/products" className="py-2 font-medium hover:text-primary transition" onClick={() => setMobileMenuOpen(false)}>All Products</Link>
              {categories.map((cat) => (
                <Link key={cat.id} to={`/category/${cat.slug}`} className="py-2 font-medium hover:text-primary transition" onClick={() => setMobileMenuOpen(false)}>
                  {cat.name}
                </Link>
              ))}
            </nav>
          </div>
        </div>
      )}
    </header>
  );
}
