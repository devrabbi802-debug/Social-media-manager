import React from 'react';
import { Plus, Edit2, Trash2, Home, MapPinned } from 'lucide-react';
import { addresses } from './data';

export default function DashboardAddresses() {
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
