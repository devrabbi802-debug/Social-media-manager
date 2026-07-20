import React, { useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { PackageSearch, Truck, Map, CheckCircle, RefreshCw, XCircle } from 'lucide-react';
import { orders, orderTracking, statusConfig } from './data';

const iconMap = { CheckCircle, Truck, RefreshCw, XCircle };

export default function DashboardTracking() {
  const [searchParams] = useSearchParams();
  const preselected = searchParams.get('order');

  const [selectedOrder, setSelectedOrder] = useState(
    preselected || orders.find(o => o.status === 'shipped' || o.status === 'processing')?.id || orders[0]?.id
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
          {orders.map((o) => {
            const cfg = statusConfig[o.status];
            return (
              <option key={o.id} value={o.id}>
                {o.id} — {o.date} ({cfg.label})
              </option>
            );
          })}
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
              <span className={`text-xs px-3 py-1 font-medium inline-flex items-center gap-1 ${statusConfig[order.status].bg} ${statusConfig[order.status].color}`}>
                {React.createElement(iconMap[statusConfig[order.status].icon], { className: 'w-3.5 h-3.5' })}
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
              <div className="bg-gray-900 h-1.5 rounded-full transition-all duration-500" style={{ width: `${(activeSteps / totalSteps) * 100}%` }} />
            </div>

            <div className="relative">
              {tracking.steps.map((step, idx) => (
                <div key={idx} className="flex gap-4 pb-6 last:pb-0 relative">
                  <div className="flex flex-col items-center">
                    <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 ${step.completed ? 'bg-gray-900 border-gray-900' : 'bg-white border-gray-300'}`}>
                      {step.completed && <CheckCircle className="w-3 h-3 text-white" />}
                    </div>
                    {idx < tracking.steps.length - 1 && (
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
