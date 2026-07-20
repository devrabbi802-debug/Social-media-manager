import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { Search, ShoppingCart, Menu, X, ChevronDown, User } from 'lucide-react';
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

export default function Header({ storeName, storeLogo }) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [scrolled, setScrolled] = useState(false);
  const navigate = useNavigate();
  const location = useLocation();
  const { openDrawer, itemCount } = useCart();
  const isHome = location.pathname === '/';

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 60);
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => {
    setMobileMenuOpen(false);
    setSearchOpen(false);
  }, [location.pathname]);

  const handleSearch = (e) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      navigate(`/products?search=${encodeURIComponent(searchQuery)}`);
      setSearchOpen(false);
    }
  };

  const headerBg = isHome && !scrolled ? 'bg-transparent' : 'bg-white shadow-md';
  const textColor = isHome && !scrolled ? 'text-white' : 'text-gray-900';
  const submenuBg = isHome && !scrolled ? 'bg-gray-900/95' : 'bg-white';
  const submenuText = isHome && !scrolled ? 'text-gray-300 hover:text-white' : 'text-gray-600 hover:text-gray-900';

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

            <Link to="/auth" className="hidden md:block p-2 hover:opacity-70 transition">
              <User className="w-5 h-5" />
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
          <div className="py-3 border-t border-gray-200">
            <form onSubmit={handleSearch} className="flex">
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search products..."
                className="flex-1 px-4 py-2 text-gray-900 border border-gray-300 focus:outline-none focus:border-gray-500"
              />
              <button type="submit" className="bg-gray-900 text-white px-6 py-2 hover:bg-gray-800 transition text-sm uppercase tracking-widest">
                Search
              </button>
            </form>
          </div>
        )}

        {mobileMenuOpen && (
          <div className="lg:hidden py-4 border-t border-gray-200 max-h-[80vh] overflow-y-auto">
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
