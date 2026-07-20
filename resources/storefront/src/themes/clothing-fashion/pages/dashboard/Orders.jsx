import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { Package, ChevronRight, CheckCircle, Truck, RefreshCw, XCircle } from 'lucide-react';
import { orders, statusConfig } from './data';

const iconMap = { CheckCircle, Truck, RefreshCw, XCircle };

export default function DashboardOrders() {
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
            const cfg = statusConfig[order.status];
            const Icon = iconMap[cfg.icon];
            return (
              <div key={order.id} className="border border-gray-100 rounded-lg p-5 hover:shadow-md transition">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                  <div className="flex-1">
                    <div className="flex items-center gap-3 mb-1">
                      <span className="text-sm font-bold text-gray-900">{order.id}</span>
                      <span className={`text-xs ${cfg.bg} ${cfg.color} px-2 py-0.5 font-medium flex items-center gap-1`}>
                        {Icon && <Icon className="w-3 h-3" />}
                        {cfg.label}
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
                        <Link
                          to={`/dashboard/tracking?order=${encodeURIComponent(order.id)}`}
                          className="text-xs bg-gray-900 text-white px-3 py-1.5 hover:bg-gray-800 transition font-medium"
                        >
                          Track
                        </Link>
                      )}
                      <Link to={`/dashboard/orders/${order.id}`} className="text-xs text-gray-400 hover:text-gray-900 transition flex items-center gap-1">
                        Details <ChevronRight className="w-3 h-3" />
                      </Link>
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
