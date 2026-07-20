import React from 'react';
import { Link, useLocation, Outlet } from 'react-router-dom';
import { User, Package, Heart, MapPin, Settings as SettingsIcon, LogOut, ChevronRight, PackageSearch } from 'lucide-react';
import { profile } from './data';

const sidebarItems = [
  { key: '', label: 'Overview', icon: User, path: '/dashboard' },
  { key: 'orders', label: 'Orders', icon: Package, path: '/dashboard/orders' },
  { key: 'tracking', label: 'Track Order', icon: PackageSearch, path: '/dashboard/tracking' },
  { key: 'wishlist', label: 'Wishlist', icon: Heart, path: '/dashboard/wishlist' },
  { key: 'addresses', label: 'Addresses', icon: MapPin, path: '/dashboard/addresses' },
  { key: 'settings', label: 'Settings', icon: SettingsIcon, path: '/dashboard/settings' },
];

export default function DashboardLayout() {
  const location = useLocation();
  const activeKey = location.pathname.replace('/dashboard/', '');

  return (
    <div className="pt-20 min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center gap-2 text-xs text-gray-400 mb-6">
          <Link to="/" className="hover:text-gray-900 transition">Home</Link>
          <ChevronRight className="w-3 h-3" />
          <span className="text-gray-900 font-medium">My Account</span>
        </div>

        <div className="flex flex-col lg:flex-row gap-8">
          <aside className="lg:w-56 flex-shrink-0">
            <div className="bg-white border border-gray-100 rounded-lg overflow-hidden sticky top-24">
              <div className="p-4 border-b border-gray-50 bg-gray-50">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                    <img src={profile.avatar} alt="" className="w-full h-full object-cover" />
                  </div>
                  <div className="min-w-0">
                    <p className="text-sm font-bold text-gray-900 line-clamp-1">{profile.name}</p>
                    <p className="text-xs text-gray-400 line-clamp-1">{profile.email}</p>
                  </div>
                </div>
              </div>
              <nav className="p-2">
                {sidebarItems.map((item) => {
                  const Icon = item.icon;
                  const isActive = activeKey === item.key;
                  return (
                    <Link
                      key={item.key}
                      to={item.path}
                      className={`w-full flex items-center gap-3 px-3 py-2.5 text-sm rounded-md transition ${
                        isActive
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <Icon className="w-4 h-4" />
                      {item.label}
                    </Link>
                  );
                })}
              </nav>
              <div className="p-2 border-t border-gray-50">
                <Link
                  to="/auth"
                  className="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-400 hover:text-red-500 transition rounded-md hover:bg-red-50"
                >
                  <LogOut className="w-4 h-4" />
                  Sign Out
                </Link>
              </div>
            </div>
          </aside>

          <main className="flex-1 min-w-0">
            <div className="bg-white border border-gray-100 rounded-lg p-4 md:p-6">
              <Outlet />
            </div>
          </main>
        </div>
      </div>
    </div>
  );
}
