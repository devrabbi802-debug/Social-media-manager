import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { LogOut } from 'lucide-react';
import { profile, orders } from './data';

export default function DashboardSettings() {
  const [form, setForm] = useState({ name: profile.name, email: profile.email, phone: profile.phone });
  const [saved, setSaved] = useState(false);

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });
  const handleSave = (e) => { e.preventDefault(); setSaved(true); setTimeout(() => setSaved(false), 3000); };

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
            <Link to="/auth" className="mt-4 text-xs text-red-500 hover:text-red-600 transition flex items-center justify-center gap-1 w-full py-2 border border-red-100 hover:border-red-200">
              <LogOut className="w-3.5 h-3.5" />
              Sign Out
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
