import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import {
  User, Package, Heart, MapPin, Settings as SettingsIcon, LogOut, ChevronRight, Star,
  ShoppingBag, CreditCard, Clock, Truck, CheckCircle, XCircle, RefreshCw,
  Plus, Edit2, Trash2, Mail, Phone, MapPinned, Home, PackageSearch, Map,
} from 'lucide-react';

const orders = [
  { id: '#ORD-2026-001', date: '15 Jun 2026', status: 'delivered', total: 5197, items: 3, payment: 'SSLCOMMERZ' },
  { id: '#ORD-2026-002', date: '28 May 2026', status: 'shipped', total: 2599, items: 1, payment: 'COD' },
  { id: '#ORD-2026-003', date: '10 May 2026', status: 'processing', total: 3798, items: 2, payment: 'SSLCOMMERZ' },
  { id: '#ORD-2026-004', date: '22 Apr 2026', status: 'cancelled', total: 1299, items: 1, payment: 'COD' },
  { id: '#ORD-2026-005', date: '05 Apr 2026', status: 'delivered', total: 8396, items: 4, payment: 'SSLCOMMERZ' },
  { id: '#ORD-2026-006', date: '18 Mar 2026', status: 'delivered', total: 1800, items: 1, payment: 'COD' },
];

const wishlistItems = [
  { id: 3, name: 'Oversized Hoodie', slug: 'oversized-hoodie', base_price: 1800, discount_price: null, effective_price: 1800, image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&q=80', inStock: true },
  { id: 10, name: 'Bomber Jacket', slug: 'bomber-jacket', base_price: 4800, discount_price: 3800, effective_price: 3800, image: 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?w=600&q=80', inStock: true },
  { id: 16, name: 'Leather Belt', slug: 'leather-belt', base_price: 900, discount_price: 699, effective_price: 699, image: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&q=80', inStock: false },
];

const addresses = [
  { id: 1, label: 'Home', name: 'Rafiq Hasan', phone: '01712-345678', address: 'House 12, Road 5, Block C', city: 'Dhaka', district: 'Mirpur', zip: '1216', isDefault: true },
  { id: 2, label: 'Office', name: 'Rafiq Hasan', phone: '01798-765432', address: 'Level 8, BTMA Bhaban', city: 'Dhaka', district: 'Motijheel', zip: '1000', isDefault: false },
];

const profile = {
  name: 'Rafiq Hasan',
  email: 'rafiq.hasan@gmail.com',
  phone: '01712-345678',
  memberSince: 'January 2026',
  avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80',
};

const recentReviews = [
  { id: 1, product: 'Classic Cotton T-Shirt', slug: 'classic-cotton-tshirt', rating: 5, date: '12 Jun 2026', text: 'Great quality fabric! Perfect fit.' },
  { id: 2, product: 'Slim Fit Denim Jeans', slug: 'slim-fit-denim-jeans', rating: 4, date: '30 May 2026', text: 'Nice jeans but slightly long for me.' },
];

const orderTracking = {
  '#ORD-2026-001': { carrier: 'Sundarban Courier', trackingId: 'BD20260615001', estimated: '18 Jun 2026', steps: [
    { label: 'Order Placed', date: '15 Jun 2026, 10:30 AM', completed: true },
    { label: 'Payment Confirmed', date: '15 Jun 2026, 10:35 AM', completed: true },
    { label: 'Processing', date: '16 Jun 2026, 09:00 AM', completed: true },
    { label: 'Shipped', date: '17 Jun 2026, 02:15 PM', completed: true },
    { label: 'Out for Delivery', date: '18 Jun 2026, 08:00 AM', completed: true },
    { label: 'Delivered', date: '18 Jun 2026, 12:45 PM', completed: true },
  ]},
  '#ORD-2026-002': { carrier: 'Pathao Courier', trackingId: 'PAT2026-05821', estimated: '30 May 2026', steps: [
    { label: 'Order Placed', date: '28 May 2026, 03:20 PM', completed: true },
    { label: 'Payment Confirmed', date: '28 May 2026, 03:25 PM', completed: true },
    { label: 'Processing', date: '29 May 2026, 10:00 AM', completed: true },
    { label: 'Shipped', date: '30 May 2026, 09:30 AM', completed: true },
    { label: 'Out for Delivery', date: null, completed: false },
    { label: 'Delivered', date: null, completed: false },
  ]},
  '#ORD-2026-003': { carrier: 'E-Desh Courier', trackingId: 'ED2026061003', estimated: '13 May 2026', steps: [
    { label: 'Order Placed', date: '10 May 2026, 11:45 AM', completed: true },
    { label: 'Payment Confirmed', date: '10 May 2026, 11:50 AM', completed: true },
    { label: 'Processing', date: '12 May 2026, 08:15 AM', completed: true },
    { label: 'Shipped', date: null, completed: false },
    { label: 'Out for Delivery', date: null, completed: false },
    { label: 'Delivered', date: null, completed: false },
  ]},
};

const sidebarItems = [
  { key: 'overview', label: 'Overview', icon: User },
  { key: 'orders', label: 'Orders', icon: Package },
  { key: 'tracking', label: 'Track Order', icon: PackageSearch },
  { key: 'wishlist', label: 'Wishlist', icon: Heart },
  { key: 'addresses', label: 'Addresses', icon: MapPin },
  { key: 'settings', label: 'Settings', icon: SettingsIcon },
];

const statusConfig = {
  delivered: { icon: CheckCircle, color: 'text-green-600', bg: 'bg-green-50', label: 'Delivered' },
  shipped: { icon: Truck, color: 'text-blue-600', bg: 'bg-blue-50', label: 'Shipped' },
  processing: { icon: RefreshCw, color: 'text-amber-600', bg: 'bg-amber-50', label: 'Processing' },
  cancelled: { icon: XCircle, color: 'text-red-600', bg: 'bg-red-50', label: 'Cancelled' },
};

function Overview({ profile, orders, wishlistItems, recentReviews }) {
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
            <span className="text-xs text-gray-400">{orders.length} total</span>
          </div>
          <div className="space-y-3">
            {orders.slice(0, 3).map((order) => {
              const StatusIcon = statusConfig[order.status].icon;
              const statusClass = statusConfig[order.status].color;
              const statusBg = statusConfig[order.status].bg;
              const statusLabel = statusConfig[order.status].label;
              return (
                <div key={order.id} className="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                  <div>
                    <p className="text-sm font-medium text-gray-900">{order.id}</p>
                    <p className="text-xs text-gray-400">{order.date} — {order.items} item{order.items > 1 ? 's' : ''}</p>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className={`text-xs ${statusBg} ${statusClass} px-2 py-0.5 font-medium flex items-center gap-1`}>
                      <StatusIcon className="w-3 h-3" />
                      {statusLabel}
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

function Orders({ orders, onTrack }) {
  const [filter, setFilter] = useState('all');

  const filteredOrders = filter === 'all' ? orders : orders.filter(o => o.status === filter);

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-bold">My Orders</h2>
        <div className="flex gap-2 overflow-x-auto pb-1">
          {[
            { key: 'all', label: 'All' },
            { key: 'delivered', label: 'Delivered' },
            { key: 'shipped', label: 'Shipped' },
            { key: 'processing', label: 'Processing' },
            { key: 'cancelled', label: 'Cancelled' },
          ].map((tab) => (
            <button
              key={tab.key}
              onClick={() => setFilter(tab.key)}
              className={`text-xs px-3 py-1.5 font-medium uppercase tracking-wider whitespace-nowrap transition ${
                filter === tab.key ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              }`}
            >
              {tab.label}
            </button>
          ))}
        </div>
      </div>

      {filteredOrders.length === 0 ? (
        <div className="text-center py-16 border border-gray-100 rounded-lg">
          <Package className="w-12 h-12 text-gray-200 mx-auto mb-3" />
          <p className="text-gray-400 text-sm">No orders found.</p>
        </div>
      ) : (
        <div className="space-y-4">
          {filteredOrders.map((order) => {
            const StatusIcon = statusConfig[order.status].icon;
            const StatusClass = statusConfig[order.status].color;
            const StatusBg = statusConfig[order.status].bg;
            const StatusLabel = statusConfig[order.status].label;
            return (
              <div key={order.id} className="border border-gray-100 rounded-lg p-5 hover:shadow-md transition">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                  <div className="flex-1">
                    <div className="flex items-center gap-3 mb-1">
                      <span className="text-sm font-bold text-gray-900">{order.id}</span>
                      <span className={`text-xs ${StatusBg} ${StatusClass} px-2 py-0.5 font-medium flex items-center gap-1`}>
                        <StatusIcon className="w-3 h-3" />
                        {StatusLabel}
                      </span>
                    </div>
                    <p className="text-xs text-gray-400">{order.date} — {order.payment}</p>
                  </div>
                  <div className="flex items-center gap-4">
                    <div className="text-right">
                      <p className="text-sm font-bold text-gray-900">৳{order.total.toLocaleString()}</p>
                      <p className="text-xs text-gray-400">{order.items} item{order.items > 1 ? 's' : ''}</p>
                    </div>
                    <div className="flex items-center gap-2">
                      {(order.status === 'shipped' || order.status === 'processing') && (
                        <button
                          onClick={() => onTrack?.(order.id)}
                          className="text-xs bg-gray-900 text-white px-3 py-1.5 hover:bg-gray-800 transition font-medium"
                        >
                          Track
                        </button>
                      )}
                      <button className="text-xs text-gray-400 hover:text-gray-900 transition flex items-center gap-1">
                        Details
                        <ChevronRight className="w-3 h-3" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}

function WishlistView({ items }) {
  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-bold">My Wishlist</h2>
        <span className="text-xs text-gray-400">{items.length} items</span>
      </div>

      {items.length === 0 ? (
        <div className="text-center py-16 border border-gray-100 rounded-lg">
          <Heart className="w-12 h-12 text-gray-200 mx-auto mb-3" />
          <p className="text-gray-400 text-sm mb-3">Your wishlist is empty.</p>
          <Link to="/products" className="text-sm text-gray-900 underline hover:no-underline">
            Browse Products
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {items.map((item) => (
            <div key={item.id} className="group border border-gray-100 rounded-lg overflow-hidden hover:shadow-md transition">
              <Link to={`/products/${item.slug}`} className="block aspect-[3/4] bg-gray-50 relative overflow-hidden">
                <img src={item.image} alt={item.name} className="w-full h-full object-cover group-hover:scale-105 transition duration-500" />
                {!item.inStock && (
                  <span className="absolute top-2 left-2 bg-gray-900 text-white text-[10px] px-2 py-0.5 font-semibold">SOLD OUT</span>
                )}
              </Link>
              <div className="p-3">
                <Link to={`/products/${item.slug}`}>
                  <h3 className="text-sm font-medium text-gray-900 line-clamp-1 hover:text-gray-600 transition">{item.name}</h3>
                </Link>
                <div className="flex items-center gap-2 mt-1 mb-2">
                  <span className="text-sm font-bold text-gray-900">৳{item.effective_price}</span>
                  {item.discount_price && (
                    <span className="text-xs text-gray-300 line-through">৳{item.base_price}</span>
                  )}
                </div>
                <button
                  disabled={!item.inStock}
                  className="w-full py-2 text-xs font-medium uppercase tracking-wider border border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white transition disabled:opacity-40 disabled:cursor-not-allowed"
                >
                  {item.inStock ? 'Add to Cart' : 'Out of Stock'}
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

function Addresses({ addresses }) {
  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-bold">My Addresses</h2>
        <button className="flex items-center gap-1 text-xs font-medium uppercase tracking-wider bg-gray-900 text-white px-4 py-2 hover:bg-gray-800 transition">
          <Plus className="w-3.5 h-3.5" />
          Add New
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {addresses.map((addr) => (
          <div key={addr.id} className={`border rounded-lg p-5 relative ${addr.isDefault ? 'border-gray-900' : 'border-gray-100'}`}>
            {addr.isDefault && (
              <span className="absolute top-3 right-3 text-[10px] bg-gray-900 text-white px-2 py-0.5 font-medium uppercase tracking-wider">
                Default
              </span>
            )}
            <div className="flex items-center gap-2 mb-3">
              <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${addr.isDefault ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600'}`}>
                {addr.label === 'Home' ? <Home className="w-4 h-4" /> : <MapPinned className="w-4 h-4" />}
              </div>
              <span className="text-sm font-bold uppercase tracking-wider">{addr.label}</span>
            </div>
            <p className="text-sm font-medium text-gray-900">{addr.name}</p>
            <p className="text-xs text-gray-400 mt-0.5">{addr.phone}</p>
            <p className="text-xs text-gray-500 mt-1">{addr.address}</p>
            <p className="text-xs text-gray-500">{addr.city}, {addr.district} — {addr.zip}</p>
            <div className="flex items-center gap-3 mt-3 pt-3 border-t border-gray-50">
              <button className="text-xs text-gray-400 hover:text-gray-900 transition flex items-center gap-1">
                <Edit2 className="w-3 h-3" />
                Edit
              </button>
              <button className="text-xs text-gray-400 hover:text-red-500 transition flex items-center gap-1">
                <Trash2 className="w-3 h-3" />
                Delete
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

function TrackOrder({ orders, orderTracking, preselectedId }) {
  const [selectedOrder, setSelectedOrder] = useState(
    preselectedId || orders.find(o => o.status === 'shipped' || o.status === 'processing')?.id || orders[0]?.id
  );

  const tracking = orderTracking[selectedOrder];
  const order = orders.find(o => o.id === selectedOrder);

  const activeSteps = tracking?.steps?.filter(s => s.completed).length || 0;
  const totalSteps = tracking?.steps?.length || 0;

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-bold">Track Order</h2>
      </div>

      <div className="mb-6">
        <label className="text-xs text-gray-500 mb-1.5 block font-medium uppercase tracking-wider">Select Order</label>
        <select
          value={selectedOrder}
          onChange={(e) => setSelectedOrder(e.target.value)}
          className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900"
        >
          {orders.map((o) => (
            <option key={o.id} value={o.id}>
              {o.id} — {o.date} ({statusConfig[o.status].label})
            </option>
          ))}
        </select>
      </div>

      {tracking && order ? (
        <div className="border border-gray-100 rounded-lg p-6">
          <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 mb-6 border-b border-gray-100">
            <div>
              <p className="text-sm font-bold text-gray-900">{order.id}</p>
              <p className="text-xs text-gray-400 mt-0.5">{order.date} — {order.items} item{order.items > 1 ? 's' : ''}</p>
            </div>
            <div className="text-right">
              <span className={`text-xs px-3 py-1 font-medium inline-flex items-center gap-1 ${
                statusConfig[order.status].bg} ${statusConfig[order.status].color
              }`}>
                {React.createElement(statusConfig[order.status].icon, { className: 'w-3.5 h-3.5' })}
                {statusConfig[order.status].label}
              </span>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-6 border-b border-gray-100">
            <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
              <Truck className="w-5 h-5 text-gray-400" />
              <div>
                <p className="text-xs text-gray-400">Carrier</p>
                <p className="text-sm font-medium text-gray-900">{tracking.carrier}</p>
              </div>
            </div>
            <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
              <PackageSearch className="w-5 h-5 text-gray-400" />
              <div>
                <p className="text-xs text-gray-400">Tracking ID</p>
                <p className="text-sm font-medium text-gray-900">{tracking.trackingId}</p>
              </div>
            </div>
            <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
              <Map className="w-5 h-5 text-gray-400" />
              <div>
                <p className="text-xs text-gray-400">Est. Delivery</p>
                <p className="text-sm font-medium text-gray-900">{tracking.estimated}</p>
              </div>
            </div>
          </div>

          <div className="mb-2">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-sm font-bold uppercase tracking-wider">Tracking Timeline</h3>
              <span className="text-xs text-gray-400">{activeSteps} of {totalSteps} completed</span>
            </div>

            <div className="w-full bg-gray-100 rounded-full h-1.5 mb-6">
              <div
                className="bg-gray-900 h-1.5 rounded-full transition-all duration-500"
                style={{ width: `${(activeSteps / totalSteps) * 100}%` }}
              />
            </div>

            <div className="relative">
              {tracking.steps.map((step, idx) => (
                <div key={idx} className="flex gap-4 pb-6 last:pb-0 relative">
                  <div className="flex flex-col items-center">
                    <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 ${
                      step.completed
                        ? 'bg-gray-900 border-gray-900'
                        : 'bg-white border-gray-300'
                    }`}>
                      {step.completed && <CheckCircle className="w-3 h-3 text-white" />}
                    </div>
                    {idx < tracking.steps.length - 1 && (
                      <div className={`w-0.5 flex-1 min-h-[20px] ${
                        step.completed ? 'bg-gray-900' : 'bg-gray-200'
                      }`} />
                    )}
                  </div>
                  <div className="flex-1 pb-2">
                    <p className={`text-sm font-medium ${
                      step.completed ? 'text-gray-900' : 'text-gray-400'
                    }`}>
                      {step.label}
                    </p>
                    {step.date && (
                      <p className="text-xs text-gray-400 mt-0.5">{step.date}</p>
                    )}
                    {!step.completed && idx === activeSteps && (
                      <p className="text-xs text-amber-600 font-medium mt-0.5 flex items-center gap-1">
                        <RefreshCw className="w-3 h-3" />
                        In Progress
                      </p>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      ) : (
        <div className="text-center py-16 border border-gray-100 rounded-lg">
          <PackageSearch className="w-12 h-12 text-gray-200 mx-auto mb-3" />
          <p className="text-gray-400 text-sm">Tracking information not available for this order.</p>
        </div>
      )}
    </div>
  );
}

function AccountSettings({ profile }) {
  const [form, setForm] = useState({ name: profile.name, email: profile.email, phone: profile.phone });
  const [saved, setSaved] = useState(false);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSave = (e) => {
    e.preventDefault();
    setSaved(true);
    setTimeout(() => setSaved(false), 3000);
  };

  return (
    <div>
      <h2 className="text-xl font-bold mb-6">Account Settings</h2>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2">
          <form onSubmit={handleSave} className="border border-gray-100 rounded-lg p-6 space-y-5">
            <div>
              <label className="text-xs text-gray-500 mb-1 block font-medium uppercase tracking-wider">Full Name</label>
              <input name="name" value={form.name} onChange={handleChange} className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900 transition" />
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="text-xs text-gray-500 mb-1 block font-medium uppercase tracking-wider">Email</label>
                <input name="email" type="email" value={form.email} onChange={handleChange} className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900 transition" />
              </div>
              <div>
                <label className="text-xs text-gray-500 mb-1 block font-medium uppercase tracking-wider">Phone</label>
                <input name="phone" type="tel" value={form.phone} onChange={handleChange} className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900 transition" />
              </div>
            </div>
            <div className="flex items-center gap-3">
              <button type="submit" className="bg-gray-900 text-white px-6 py-2.5 text-sm font-medium hover:bg-gray-800 transition uppercase tracking-wider">
                Save Changes
              </button>
              {saved && <span className="text-xs text-green-600 font-medium">Changes saved successfully!</span>}
            </div>
          </form>

          <div className="border border-gray-100 rounded-lg p-6 mt-6">
            <h3 className="text-sm font-bold uppercase tracking-wider mb-2">Change Password</h3>
            <p className="text-xs text-gray-400 mb-4">Update your password to keep your account secure.</p>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
              <input type="password" placeholder="Current Password" className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900 transition" />
              <input type="password" placeholder="New Password" className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900 transition" />
              <input type="password" placeholder="Confirm Password" className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900 transition" />
            </div>
            <button className="border border-gray-900 text-gray-900 px-6 py-2.5 text-sm font-medium hover:bg-gray-900 hover:text-white transition uppercase tracking-wider">
              Update Password
            </button>
          </div>
        </div>

        <div className="lg:col-span-1">
          <div className="border border-gray-100 rounded-lg p-6 text-center sticky top-28">
            <div className="w-20 h-20 rounded-full overflow-hidden mx-auto mb-4 border-2 border-gray-100">
              <img src={profile.avatar} alt="" className="w-full h-full object-cover" />
            </div>
            <h3 className="text-sm font-bold text-gray-900">{profile.name}</h3>
            <p className="text-xs text-gray-400 mt-1">{profile.email}</p>
            <div className="mt-4 pt-4 border-t border-gray-50">
              <div className="flex items-center justify-between text-xs py-2">
                <span className="text-gray-400">Member Since</span>
                <span className="text-gray-900 font-medium">{profile.memberSince}</span>
              </div>
              <div className="flex items-center justify-between text-xs py-2">
                <span className="text-gray-400">Orders</span>
                <span className="text-gray-900 font-medium">{orders.length}</span>
              </div>
            </div>
            <button className="mt-4 text-xs text-red-500 hover:text-red-600 transition flex items-center justify-center gap-1 w-full py-2 border border-red-100 hover:border-red-200">
              <LogOut className="w-3.5 h-3.5" />
              Sign Out
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default function Dashboard() {
  const [activeSection, setActiveSection] = useState('overview');
  const [trackingOrderId, setTrackingOrderId] = useState(null);

  const handleTrack = (orderId) => {
    setTrackingOrderId(orderId);
    setActiveSection('tracking');
  };

  const renderSection = () => {
    switch (activeSection) {
      case 'overview': return <Overview profile={profile} orders={orders} wishlistItems={wishlistItems} recentReviews={recentReviews} />;
      case 'orders': return <Orders orders={orders} onTrack={handleTrack} />;
      case 'tracking': return <TrackOrder orders={orders} orderTracking={orderTracking} preselectedId={trackingOrderId} />;
      case 'wishlist': return <WishlistView items={wishlistItems} />;
      case 'addresses': return <Addresses addresses={addresses} />;
      case 'settings': return <AccountSettings profile={profile} />;
      default: return <Overview profile={profile} orders={orders} wishlistItems={wishlistItems} recentReviews={recentReviews} />;
    }
  };

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
                  return (
                    <button
                      key={item.key}
                      onClick={() => setActiveSection(item.key)}
                      className={`w-full flex items-center gap-3 px-3 py-2.5 text-sm rounded-md transition ${
                        activeSection === item.key
                          ? 'bg-gray-900 text-white font-medium'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <Icon className="w-4 h-4" />
                      {item.label}
                    </button>
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
              {renderSection()}
            </div>
          </main>
        </div>
      </div>
    </div>
  );
}
