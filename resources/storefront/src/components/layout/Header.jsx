import React, { useState } from 'react';
import { Search, ShoppingCart, Menu, X, ChevronDown } from 'lucide-react';

export default function Header({ storeName, storeLogo, categories = [] }) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');

  const handleSearch = (e) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      window.location.href = `/products?search=${encodeURIComponent(searchQuery)}`;
    }
  };

  return (
    <header className="bg-headerBg header-text sticky top-0 z-50" style={{ color: 'var(--color-header_text, #FFFFFF)' }}>
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <div className="flex items-center">
            <a href="/" className="flex items-center space-x-2">
              {storeLogo ? (
                <img src={storeLogo} alt={storeName} className="h-8 w-auto object-contain" />
              ) : (
                <span className="text-xl font-bold">{storeName || 'Store'}</span>
              )}
            </a>
          </div>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center space-x-8">
            <a href="/" className="hover:text-primary transition">Home</a>
            <div className="relative group">
              <button className="flex items-center space-x-1 hover:text-primary transition">
                <span>Categories</span>
                <ChevronDown className="w-4 h-4" />
              </button>
              {categories.length > 0 && (
                <div className="absolute top-full left-0 mt-2 w-48 bg-white text-gray-900 rounded-lg shadow-lg py-2 hidden group-hover:block">
                  {categories.map((cat) => (
                    <a
                      key={cat.id}
                      href={`/category/${cat.slug}`}
                      className="block px-4 py-2 hover:bg-gray-100 transition"
                    >
                      {cat.name}
                    </a>
                  ))}
                </div>
              )}
            </div>
            <a href="/products" className="hover:text-primary transition">All Products</a>
          </nav>

          {/* Right Side */}
          <div className="flex items-center space-x-4">
            {/* Search Toggle */}
            <button
              onClick={() => setSearchOpen(!searchOpen)}
              className="p-2 hover:opacity-80 rounded-lg transition"
            >
              <Search className="w-5 h-5" />
            </button>

            {/* Cart */}
            <a href="/cart" className="p-2 hover:opacity-80 rounded-lg transition relative">
              <ShoppingCart className="w-5 h-5" />
              <span className="absolute -top-1 -right-1 bg-primary text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
                0
              </span>
            </a>

            {/* Mobile Menu Toggle */}
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="md:hidden p-2 hover:opacity-80 rounded-lg transition"
            >
              {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </button>
          </div>
        </div>

        {/* Search Bar */}
        {searchOpen && (
          <div className="py-3 border-t" style={{ borderColor: 'var(--color-header_text, #FFFFFF)' + '33' }}>
            <form onSubmit={handleSearch} className="flex">
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search products..."
                className="flex-1 px-4 py-2 rounded-l-lg text-gray-900 focus:outline-none"
              />
              <button
                type="submit"
                className="bg-primary px-6 py-2 rounded-r-lg hover:bg-primary/90 transition"
              >
                Search
              </button>
            </form>
          </div>
        )}

        {/* Mobile Menu */}
        {mobileMenuOpen && (
          <div className="md:hidden py-4 border-t" style={{ borderColor: 'var(--color-header_text, #FFFFFF)' + '33' }}>
            <nav className="flex flex-col space-y-2">
              <a href="/" className="py-2 hover:text-primary transition">Home</a>
              <a href="/products" className="py-2 hover:text-primary transition">All Products</a>
              {categories.map((cat) => (
                <a
                  key={cat.id}
                  href={`/category/${cat.slug}`}
                  className="py-2 hover:text-primary transition"
                >
                  {cat.name}
                </a>
              ))}
            </nav>
          </div>
        )}
      </div>
    </header>
  );
}