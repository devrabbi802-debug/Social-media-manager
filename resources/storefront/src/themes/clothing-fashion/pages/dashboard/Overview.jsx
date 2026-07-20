import React from 'react';
import { Link } from 'react-router-dom';
import {
  User, Package, CheckCircle, Heart, CreditCard, Star, LogOut, ChevronRight,
  Truck, RefreshCw, XCircle,
} from 'lucide-react';
import { profile, orders, wishlistItems, recentReviews, statusConfig } from './data';

const iconMap = { CheckCircle, Truck, RefreshCw, XCircle };

export default function DashboardHome() {
  const deliveredOrders = orders.filter(o => o.status === 'delivered').length;
  const totalSpent = orders.filter(o => o.status === 'delivered').reduce((sum, o) => sum + o.total, 0);

  return (
    <div>
      <div className="flex items-center gap-4 mb-8 p-6 bg-gradient-to-r from-gray-900 to-gray-800 text-white rounded-lg">
        <div className="w-16 h-16 rounded-full overflow-hidden border-2 border-white/30 flex-shrink-0">
          <img src={profile.avatar} alt="" className="w-full h-full object-cover" />
        </div>
        <div className="flex-1">
          <h2 className="text-xl font-bold">{profile.name}</h2>
          <p className="text-sm text-white/70">{profile.email}</p>
          <p className="text-xs text-white/50 mt-1">Member since {profile.memberSince}</p>
        </div>
        <Link to="/auth" className="text-xs text-white/60 hover:text-white transition flex items-center gap-1">
          <LogOut className="w-3.5 h-3.5" />
          Sign Out
        </Link>
      </div>

      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        {[
          { icon: Package, label: 'Total Orders', value: orders.length, color: 'text-blue-600', bg: 'bg-blue-50' },
          { icon: CheckCircle, label: 'Delivered', value: deliveredOrders, color: 'text-green-600', bg: 'bg-green-50' },
          { icon: Heart, label: 'Wishlist', value: wishlistItems.length, color: 'text-red-500', bg: 'bg-red-50' },
          { icon: CreditCard, label: 'Total Spent', value: `৳${totalSpent.toLocaleString()}`, color: 'text-purple-600', bg: 'bg-purple-50' },
        ].map((stat, i) => {
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
            <Link to="/dashboard/orders" className="text-xs text-gray-400 hover:text-gray-900 transition flex items-center gap-1">
              View All <ChevronRight className="w-3 h-3" />
            </Link>
          </div>
          <div className="space-y-3">
            {orders.slice(0, 3).map((order) => {
              const cfg = statusConfig[order.status];
              const Icon = iconMap[cfg.icon];
              return (
                <div key={order.id} className="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                  <div>
                    <p className="text-sm font-medium text-gray-900">{order.id}</p>
                    <p className="text-xs text-gray-400">{order.date} — {order.items} item{order.items > 1 ? 's' : ''}</p>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className={`text-xs ${cfg.bg} ${cfg.color} px-2 py-0.5 font-medium flex items-center gap-1`}>
                      {Icon && <Icon className="w-3 h-3" />}
                      {cfg.label}
                    </span>
                    <span className="text-sm font-bold">৳{order.total.toLocaleString()}</span>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        <div className="border border-gray-100 rounded-lg p-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-sm font-bold uppercase tracking-wider">Recent Reviews</h3>
          </div>
          {recentReviews.length === 0 ? (
            <p className="text-sm text-gray-400">No reviews yet.</p>
          ) : (
            <div className="space-y-4">
              {recentReviews.map((review) => (
                <div key={review.id} className="pb-3 border-b border-gray-50 last:border-0">
                  <div className="flex items-center justify-between mb-1">
                    <Link to={`/products/${review.slug}`} className="text-sm font-medium text-gray-900 hover:text-gray-600 transition">
                      {review.product}
                    </Link>
                    <span className="text-xs text-gray-400">{review.date}</span>
                  </div>
                  <div className="flex items-center gap-0.5 mb-1">
                    {[...Array(5)].map((_, i) => (
                      <Star key={i} className={`w-3 h-3 ${i < review.rating ? 'fill-amber-400 text-amber-400' : 'text-gray-200'}`} />
                    ))}
                  </div>
                  <p className="text-xs text-gray-500">{review.text}</p>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
