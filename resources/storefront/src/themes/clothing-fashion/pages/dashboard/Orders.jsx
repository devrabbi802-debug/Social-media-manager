import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Package, ChevronRight } from 'lucide-react';
import api from '../../../../api/client';

const statusFilter = [
  { key: 'all', label: 'All' },
  { key: 'delivered', label: 'Delivered' },
  { key: 'shipped', label: 'Shipped' },
  { key: 'processing', label: 'Processing' },
  { key: 'cancelled', label: 'Cancelled' },
];

export default function Orders() {
  const navigate = useNavigate();
  const [orders, setOrders] = useState([]);
  const [filter, setFilter] = useState('all');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetch = async () => {
      setLoading(true);
      try {
        const params = filter !== 'all' ? { status: filter } : {};
        const res = await api.get('/customer/orders', { params });
        setOrders(res?.data || []);
      } catch {} finally {
        setLoading(false);
      }
    };
    fetch();
  }, [filter]);

  const filteredOrders = filter === 'all' ? orders : orders.filter((o) => o.status === filter);

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-bold">My Orders</h2>
        <div className="flex gap-2 overflow-x-auto pb-1">
          {statusFilter.map((tab) => (
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

      {loading ? (
        <div className="flex justify-center py-16">
          <div className="w-8 h-8 border-2 border-gray-900 border-t-transparent rounded-full animate-spin" />
        </div>
      ) : filteredOrders.length === 0 ? (
        <div className="text-center py-16 border border-gray-100 rounded-lg">
          <Package className="w-12 h-12 text-gray-200 mx-auto mb-3" />
          <p className="text-gray-400 text-sm">No orders found.</p>
        </div>
      ) : (
        <div className="space-y-4">
          {filteredOrders.map((order) => (
            <div key={order.id} className="border border-gray-100 rounded-lg p-5 hover:shadow-md transition">
              <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div className="flex-1">
                  <div className="flex items-center gap-3 mb-1">
                    <span className="text-sm font-bold text-gray-900">
                      {order.order_number || `#ORD-${order.id}`}
                    </span>
                    <span className={`text-xs px-2 py-0.5 font-medium capitalize ${
                      order.status === 'delivered' ? 'bg-green-50 text-green-600' :
                      order.status === 'shipped' ? 'bg-blue-50 text-blue-600' :
                      order.status === 'processing' ? 'bg-amber-50 text-amber-600' :
                      order.status === 'cancelled' ? 'bg-red-50 text-red-600' :
                      'bg-gray-50 text-gray-600'
                    }`}>
                      {order.status}
                    </span>
                  </div>
                  <p className="text-xs text-gray-400">
                    {order.created_at?.slice(0, 10)} — {order.payment_method || 'N/A'}
                  </p>
                </div>
                <div className="flex items-center gap-4">
                  <div className="text-right">
                    <p className="text-sm font-bold text-gray-900">৳{(order.total || 0).toLocaleString()}</p>
                    <p className="text-xs text-gray-400">{order.items_count || 0} items</p>
                  </div>
                  <button
                    onClick={() => navigate(`/dashboard/orders/${order.id}`)}
                    className="text-xs text-gray-400 hover:text-gray-900 transition flex items-center gap-1"
                  >
                    Details
                    <ChevronRight className="w-3 h-3" />
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
