import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { Package, CheckCircle, Heart, CreditCard, LogOut, Star } from 'lucide-react';
import api from '../../../../api/client';
import { useAuth } from '../../../../contexts/AuthContext';

export default function Overview() {
  const { user, logout } = useAuth();
  const [stats, setStats] = useState(null);
  const [recentOrders, setRecentOrders] = useState([]);
  const [recentReviews, setRecentReviews] = useState([]);

  useEffect(() => {
    const fetch = async () => {
      try {
        const [statsRes, ordersRes, reviewsRes] = await Promise.all([
          api.get('/customer/dashboard-stats'),
          api.get('/customer/orders?per_page=3'),
          api.get('/customer/reviews'),
        ]);
        setStats(statsRes);
        setRecentOrders(ordersRes?.data || []);
        setRecentReviews(reviewsRes || []);
      } catch {}
    };
    fetch();
  }, []);

  const statCards = stats ? [
    { icon: Package, label: 'Total Orders', value: stats.total_orders, color: 'text-blue-600', bg: 'bg-blue-50' },
    { icon: CheckCircle, label: 'Delivered', value: stats.delivered_orders, color: 'text-green-600', bg: 'bg-green-50' },
    { icon: Heart, label: 'Wishlist', value: stats.wishlist_count, color: 'text-red-500', bg: 'bg-red-50' },
    { icon: CreditCard, label: 'Total Spent', value: `৳${(stats.total_spent || 0).toLocaleString()}`, color: 'text-purple-600', bg: 'bg-purple-50' },
  ] : [];

  return (
    <div>
      <div className="flex items-center gap-4 mb-8 p-6 bg-gradient-to-r from-gray-900 to-gray-800 text-white rounded-lg">
        <div className="w-14 h-14 rounded-full overflow-hidden border-2 border-white/30 flex-shrink-0 bg-gray-700 flex items-center justify-center">
          <span className="text-lg font-bold text-white/80">
            {user?.name?.charAt(0)?.toUpperCase() || 'U'}
          </span>
        </div>
        <div className="flex-1">
          <h2 className="text-xl font-bold">{user?.name || 'User'}</h2>
          <p className="text-sm text-white/70">{user?.email}</p>
        </div>
        <button
          onClick={logout}
          className="text-xs text-white/60 hover:text-white transition flex items-center gap-1"
        >
          <LogOut className="w-3.5 h-3.5" />
          Sign Out
        </button>
      </div>

      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        {statCards.map((stat, i) => {
          const Icon = stat.icon;
          return (
            <div key={i} className="border border-gray-100 p-4 rounded-lg hover:shadow-md transition">
              <div className={`w-10 h-10 ${stat.bg} rounded-lg flex items-center justify-center mb-3`}>
                <Icon className={`w-5 h-5 ${stat.color}`} />
              </div>
              <p className="text-2xl font-bold text-gray-900">{stat.value}</p>
              <p className="text-xs text-gray-400 mt-1">{stat.label}</p>
            </div>
          );
        })}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="border border-gray-100 rounded-lg p-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-sm font-bold uppercase tracking-wider">Recent Orders</h3>
            <Link to="/dashboard/orders" className="text-xs text-gray-400 hover:text-gray-900 transition">
              View All
            </Link>
          </div>
          {recentOrders.length === 0 ? (
            <p className="text-sm text-gray-400 py-4 text-center">No orders yet.</p>
          ) : (
            <div className="space-y-3">
              {recentOrders.map((order) => (
                <div key={order.id} className="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                  <div>
                    <p className="text-sm font-medium text-gray-900">{order.order_number || `#ORD-${order.id}`}</p>
                    <p className="text-xs text-gray-400">{order.created_at?.slice(0, 10)} — {order.items_count || 0} items</p>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className="text-xs capitalize text-gray-500 bg-gray-50 px-2 py-0.5 font-medium">
                      {order.status}
                    </span>
                    <span className="text-sm font-bold">৳{(order.total || 0).toLocaleString()}</span>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        <div className="border border-gray-100 rounded-lg p-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-sm font-bold uppercase tracking-wider">Recent Reviews</h3>
          </div>
          {recentReviews.length === 0 ? (
            <p className="text-sm text-gray-400 py-4 text-center">No reviews yet.</p>
          ) : (
            <div className="space-y-4">
              {recentReviews.map((review) => (
                <div key={review.id} className="pb-3 border-b border-gray-50 last:border-0">
                  <p className="text-sm font-medium text-gray-900 mb-1">{review.product?.name || 'Product'}</p>
                  <div className="flex items-center gap-0.5 mb-1">
                    {[...Array(5)].map((_, i) => (
                      <Star key={i} className={`w-3 h-3 ${i < review.rating ? 'fill-amber-400 text-amber-400' : 'text-gray-200'}`} />
                    ))}
                  </div>
                  {review.text && <p className="text-xs text-gray-500">{review.text}</p>}
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
