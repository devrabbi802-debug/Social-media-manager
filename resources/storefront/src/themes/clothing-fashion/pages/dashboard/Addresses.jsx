import React, { useState, useEffect } from 'react';
import { Plus, Edit2, Trash2, Home, MapPinned } from 'lucide-react';
import api from '../../../../api/client';

function AddressForm({ address, onSave, onCancel }) {
  const [form, setForm] = useState({
    label: address?.label || '',
    name: address?.name || '',
    phone: address?.phone || '',
    address: address?.address || '',
    city: address?.city || '',
    district: address?.district || '',
    zip: address?.zip || '',
    is_default: address?.is_default || false,
  });
  const [saving, setSaving] = useState(false);

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setForm((prev) => ({ ...prev, [name]: type === 'checkbox' ? checked : value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    try {
      if (address) {
        await api.put(`/customer/addresses/${address.id}`, form);
      } else {
        await api.post('/customer/addresses', form);
      }
      onSave();
    } catch {} finally {
      setSaving(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="border border-gray-100 rounded-lg p-5 space-y-4">
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="text-xs text-gray-500 mb-1 block font-medium uppercase tracking-wider">Label</label>
          <select name="label" value={form.label} onChange={handleChange} className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900">
            <option value="">Select...</option>
            <option value="Home">Home</option>
            <option value="Office">Office</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div>
          <label className="text-xs text-gray-500 mb-1 block font-medium uppercase tracking-wider">Full Name</label>
          <input name="name" value={form.name} onChange={handleChange} required className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
        </div>
      </div>
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="text-xs text-gray-500 mb-1 block font-medium uppercase tracking-wider">Phone</label>
          <input name="phone" value={form.phone} onChange={handleChange} required className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
        </div>
        <div>
          <label className="text-xs text-gray-500 mb-1 block font-medium uppercase tracking-wider">City</label>
          <input name="city" value={form.city} onChange={handleChange} required className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
        </div>
      </div>
      <div>
        <label className="text-xs text-gray-500 mb-1 block font-medium uppercase tracking-wider">Address</label>
        <input name="address" value={form.address} onChange={handleChange} required className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
      </div>
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="text-xs text-gray-500 mb-1 block font-medium uppercase tracking-wider">District</label>
          <input name="district" value={form.district} onChange={handleChange} required className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
        </div>
        <div>
          <label className="text-xs text-gray-500 mb-1 block font-medium uppercase tracking-wider">ZIP Code</label>
          <input name="zip" value={form.zip} onChange={handleChange} className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900" />
        </div>
      </div>
      <div className="flex items-center gap-2">
        <input type="checkbox" name="is_default" checked={form.is_default} onChange={handleChange} id="is_default" className="w-4 h-4 border-gray-300" />
        <label htmlFor="is_default" className="text-sm text-gray-600">Set as default address</label>
      </div>
      <div className="flex items-center gap-3 pt-2">
        <button type="submit" disabled={saving} className="bg-gray-900 text-white px-5 py-2.5 text-sm font-medium hover:bg-gray-800 transition disabled:opacity-50">
          {saving ? 'Saving...' : address ? 'Update' : 'Add Address'}
        </button>
        <button type="button" onClick={onCancel} className="text-sm text-gray-400 hover:text-gray-900 transition">Cancel</button>
      </div>
    </form>
  );
}

export default function Addresses() {
  const [addresses, setAddresses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [editing, setEditing] = useState(null);

  const fetchAddresses = async () => {
    try {
      const res = await api.get('/customer/addresses');
      setAddresses(res || []);
    } catch {} finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchAddresses(); }, []);

  const handleSave = () => {
    setShowForm(false);
    setEditing(null);
    fetchAddresses();
  };

  const handleDelete = async (id) => {
    if (!confirm('Delete this address?')) return;
    try {
      await api.delete(`/customer/addresses/${id}`);
      fetchAddresses();
    } catch {}
  };

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-bold">My Addresses</h2>
        <button
          onClick={() => { setEditing(null); setShowForm(!showForm); }}
          className="flex items-center gap-1 text-xs font-medium uppercase tracking-wider bg-gray-900 text-white px-4 py-2 hover:bg-gray-800 transition"
        >
          <Plus className="w-3.5 h-3.5" />
          {showForm ? 'Cancel' : 'Add New'}
        </button>
      </div>

      {showForm && (
        <div className="mb-6">
          <AddressForm address={editing} onSave={handleSave} onCancel={() => { setShowForm(false); setEditing(null); }} />
        </div>
      )}

      {loading ? (
        <div className="flex justify-center py-16">
          <div className="w-8 h-8 border-2 border-gray-900 border-t-transparent rounded-full animate-spin" />
        </div>
      ) : addresses.length === 0 ? (
        <div className="text-center py-16 border border-gray-100 rounded-lg">
          <MapPinned className="w-12 h-12 text-gray-200 mx-auto mb-3" />
          <p className="text-gray-400 text-sm">No addresses saved yet.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {addresses.map((addr) => (
            <div key={addr.id} className={`border rounded-lg p-5 relative ${addr.is_default ? 'border-gray-900' : 'border-gray-100'}`}>
              {addr.is_default && (
                <span className="absolute top-3 right-3 text-[10px] bg-gray-900 text-white px-2 py-0.5 font-medium uppercase tracking-wider">
                  Default
                </span>
              )}
              <div className="flex items-center gap-2 mb-3">
                <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${addr.is_default ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600'}`}>
                  {addr.label === 'Home' ? <Home className="w-4 h-4" /> : <MapPinned className="w-4 h-4" />}
                </div>
                <span className="text-sm font-bold uppercase tracking-wider">{addr.label || 'Address'}</span>
              </div>
              <p className="text-sm font-medium text-gray-900">{addr.name}</p>
              <p className="text-xs text-gray-400 mt-0.5">{addr.phone}</p>
              <p className="text-xs text-gray-500 mt-1">{addr.address}</p>
              <p className="text-xs text-gray-500">{addr.city}, {addr.district}{addr.zip ? ` — ${addr.zip}` : ''}</p>
              <div className="flex items-center gap-3 mt-3 pt-3 border-t border-gray-50">
                <button
                  onClick={() => { setEditing(addr); setShowForm(true); }}
                  className="text-xs text-gray-400 hover:text-gray-900 transition flex items-center gap-1"
                >
                  <Edit2 className="w-3 h-3" /> Edit
                </button>
                <button
                  onClick={() => handleDelete(addr.id)}
                  className="text-xs text-gray-400 hover:text-red-500 transition flex items-center gap-1"
                >
                  <Trash2 className="w-3 h-3" /> Delete
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
