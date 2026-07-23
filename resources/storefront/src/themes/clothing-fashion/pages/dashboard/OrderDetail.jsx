import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { ArrowLeft, Package, Truck, MapPin } from 'lucide-react';
import api from '../../../../api/client';

export default function OrderDetail() {
  const { id } = useParams();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetch = async () => {
      try {
        const res = await api.get(`/customer/orders/${id}`);
        setOrder(res?.data);
      } catch {} finally {
        setLoading(false);
      }
    };
    fetch();
  }, [id]);

  if (loading) {
    return (
      <div className="flex justify-center py-16">
        <div className="w-8 h-8 border-2 border-gray-900 border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  if (!order) {
    return (
      <div className="text-center py-16">
        <Package className="w-12 h-12 text-gray-200 mx-auto mb-3" />
        <p className="text-gray-400 text-sm">Order not found.</p>
        <Link to="/dashboard/orders" className="text-sm text-gray-900 underline mt-2 inline-block">
          Back to Orders
        </Link>
      </div>
    );
  }

  return (
    <div>
      <div className="flex items-center gap-2 text-xs text-gray-400 mb-6">
        <Link to="/dashboard/orders" className="hover:text-gray-900 transition">Orders</Link>
        <ArrowLeft className="w-3 h-3" />
        <span className="text-gray-900 font-medium">{order.order_number || `#ORD-${order.id}`}</span>
      </div>

      <div className="border border-gray-100 rounded-lg p-6 mb-6">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-lg font-bold">{order.order_number || `#ORD-${order.id}`}</h2>
          <span className={`text-xs px-3 py-1 font-medium capitalize ${
            order.status === 'delivered' ? 'bg-green-50 text-green-600' :
            order.status === 'shipped' ? 'bg-blue-50 text-blue-600' :
            order.status === 'processing' ? 'bg-amber-50 text-amber-600' :
            order.status === 'cancelled' ? 'bg-red-50 text-red-600' :
            'bg-gray-50 text-gray-600'
          }`}>
            {order.status}
          </span>
        </div>
        <p className="text-xs text-gray-400 mb-4">{order.created_at?.slice(0, 10)}</p>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
          <div>
            <p className="text-xs text-gray-400 uppercase tracking-wider">Subtotal</p>
            <p className="text-sm font-bold">৳{(order.subtotal || 0).toLocaleString()}</p>
          </div>
          <div>
            <p className="text-xs text-gray-400 uppercase tracking-wider">Shipping</p>
            <p className="text-sm font-bold">৳{(order.shipping_cost || 0).toLocaleString()}</p>
          </div>
          <div>
            <p className="text-xs text-gray-400 uppercase tracking-wider">Total</p>
            <p className="text-sm font-bold text-gray-900">৳{(order.total || 0).toLocaleString()}</p>
          </div>
          <div>
            <p className="text-xs text-gray-400 uppercase tracking-wider">Payment</p>
            <p className="text-sm font-bold">{order.payment_method || 'N/A'}</p>
          </div>
        </div>

        {order.carrier && (
          <div className="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
            <Truck className="w-4 h-4 text-gray-400" />
            <span className="text-xs text-gray-600">Carrier: {order.carrier}</span>
            {order.tracking_id && (
              <span className="text-xs text-gray-400">— Tracking: {order.tracking_id}</span>
            )}
          </div>
        )}
      </div>

      <div className="border border-gray-100 rounded-lg p-6">
        <h3 className="text-sm font-bold uppercase tracking-wider mb-4">Order Items</h3>
        <div className="space-y-4">
          {(order.items || []).map((item, i) => (
            <div key={item.id || i} className="flex items-center gap-4 pb-4 border-b border-gray-50 last:border-0">
              <div className="w-16 h-20 bg-gray-50 overflow-hidden flex-shrink-0">
                <img
                  src={item.product?.image || item.image || 'https://placehold.co/64x80?text=Item'}
                  alt={item.name}
                  className="w-full h-full object-cover"
                />
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900">{item.name || item.product?.name}</p>
                <p className="text-xs text-gray-400">Qty: {item.quantity} × ৳{(item.unit_price || 0).toLocaleString()}</p>
              </div>
              <p className="text-sm font-bold">৳{(item.total_price || 0).toLocaleString()}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
