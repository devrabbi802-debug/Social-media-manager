import React, { useState, useEffect } from 'react';
import { PackageSearch, Truck, Map, RefreshCw, CheckCircle } from 'lucide-react';
import api from '../../../../api/client';

export default function Tracking() {
  const [orders, setOrders] = useState([]);
  const [selectedOrder, setSelectedOrder] = useState(null);
  const [tracking, setTracking] = useState(null);
  const [loading, setLoading] = useState(true);
  const [trackingLoading, setTrackingLoading] = useState(false);

  useEffect(() => {
    const fetchOrders = async () => {
      try {
        const res = await api.get('/customer/orders');
        const data = res?.data || [];
        setOrders(data);
        if (data.length > 0) setSelectedOrder(data[0].id);
      } catch {} finally {
        setLoading(false);
      }
    };
    fetchOrders();
  }, []);

  useEffect(() => {
    if (!selectedOrder) return;
    const fetchTracking = async () => {
      setTrackingLoading(true);
      try {
        const res = await api.get(`/customer/orders/${selectedOrder}/tracking`);
        setTracking(res?.data || null);
      } catch {
        setTracking(null);
      } finally {
        setTrackingLoading(false);
      }
    };
    fetchTracking();
  }, [selectedOrder]);

  const order = orders.find((o) => o.id === selectedOrder);
  const steps = tracking?.tracking_steps || [];
  const activeSteps = steps.filter((s) => s.completed).length;
  const totalSteps = steps.length;

  return (
    <div>
      <h2 className="text-xl font-bold mb-6">Track Order</h2>

      {loading ? (
        <div className="flex justify-center py-16">
          <div className="w-8 h-8 border-2 border-gray-900 border-t-transparent rounded-full animate-spin" />
        </div>
      ) : (
        <>
          <div className="mb-6">
            <label className="text-xs text-gray-500 mb-1.5 block font-medium uppercase tracking-wider">
              Select Order
            </label>
            <select
              value={selectedOrder || ''}
              onChange={(e) => setSelectedOrder(Number(e.target.value))}
              className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900"
            >
              {orders.map((o) => (
                <option key={o.id} value={o.id}>
                  {o.order_number || `#ORD-${o.id}`} — {o.created_at?.slice(0, 10)} ({o.status})
                </option>
              ))}
            </select>
          </div>

          {trackingLoading ? (
            <div className="flex justify-center py-16">
              <div className="w-8 h-8 border-2 border-gray-900 border-t-transparent rounded-full animate-spin" />
            </div>
          ) : tracking ? (
            <div className="border border-gray-100 rounded-lg p-6">
              <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 mb-6 border-b border-gray-100">
                <div>
                  <p className="text-sm font-bold text-gray-900">
                    {order?.order_number || `#ORD-${order?.id}`}
                  </p>
                  <p className="text-xs text-gray-400 mt-0.5">{order?.created_at?.slice(0, 10)}</p>
                </div>
                <span className={`text-xs px-3 py-1 font-medium capitalize ${
                  order?.status === 'delivered' ? 'bg-green-50 text-green-600' :
                  order?.status === 'shipped' ? 'bg-blue-50 text-blue-600' :
                  order?.status === 'processing' ? 'bg-amber-50 text-amber-600' : ''
                }`}>
                  {order?.status}
                </span>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 pb-6 border-b border-gray-100">
                <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                  <Truck className="w-5 h-5 text-gray-400" />
                  <div>
                    <p className="text-xs text-gray-400">Carrier</p>
                    <p className="text-sm font-medium text-gray-900">{tracking.carrier || 'N/A'}</p>
                  </div>
                </div>
                <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                  <PackageSearch className="w-5 h-5 text-gray-400" />
                  <div>
                    <p className="text-xs text-gray-400">Tracking ID</p>
                    <p className="text-sm font-medium text-gray-900">{tracking.tracking_id || 'N/A'}</p>
                  </div>
                </div>
                <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                  <Map className="w-5 h-5 text-gray-400" />
                  <div>
                    <p className="text-xs text-gray-400">Est. Delivery</p>
                    <p className="text-sm font-medium text-gray-900">
                      {tracking.estimated_delivery ? new Date(tracking.estimated_delivery).toLocaleDateString() : 'N/A'}
                    </p>
                  </div>
                </div>
              </div>

              {steps.length > 0 && (
                <div>
                  <div className="flex items-center justify-between mb-4">
                    <h3 className="text-sm font-bold uppercase tracking-wider">Tracking Timeline</h3>
                    <span className="text-xs text-gray-400">{activeSteps} of {totalSteps} completed</span>
                  </div>

                  <div className="w-full bg-gray-100 rounded-full h-1.5 mb-6">
                    <div
                      className="bg-gray-900 h-1.5 rounded-full transition-all duration-500"
                      style={{ width: `${totalSteps > 0 ? (activeSteps / totalSteps) * 100 : 0}%` }}
                    />
                  </div>

                  <div className="relative">
                    {steps.map((step, idx) => (
                      <div key={idx} className="flex gap-4 pb-6 last:pb-0 relative">
                        <div className="flex flex-col items-center">
                          <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 ${
                            step.completed ? 'bg-gray-900 border-gray-900' : 'bg-white border-gray-300'
                          }`}>
                            {step.completed && <CheckCircle className="w-3 h-3 text-white" />}
                          </div>
                          {idx < steps.length - 1 && (
                            <div className={`w-0.5 flex-1 min-h-[20px] ${step.completed ? 'bg-gray-900' : 'bg-gray-200'}`} />
                          )}
                        </div>
                        <div className="flex-1 pb-2">
                          <p className={`text-sm font-medium ${step.completed ? 'text-gray-900' : 'text-gray-400'}`}>
                            {step.label}
                          </p>
                          {step.date && <p className="text-xs text-gray-400 mt-0.5">{step.date}</p>}
                          {!step.completed && idx === activeSteps && (
                            <p className="text-xs text-amber-600 font-medium mt-0.5 flex items-center gap-1">
                              <RefreshCw className="w-3 h-3" /> In Progress
                            </p>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          ) : (
            <div className="text-center py-16 border border-gray-100 rounded-lg">
              <PackageSearch className="w-12 h-12 text-gray-200 mx-auto mb-3" />
              <p className="text-gray-400 text-sm">Tracking information not available.</p>
            </div>
          )}
        </>
      )}
    </div>
  );
}
