import React, { useState } from 'react';
import { LogOut } from 'lucide-react';
import { useAuth } from '../../../../contexts/AuthContext';

export default function Settings() {
  const { user, updateProfile, updatePassword, logout } = useAuth();

  const [profile, setProfile] = useState({
    name: user?.name || '',
    email: user?.email || '',
    phone: user?.phone || '',
  });
  const [profileSaved, setProfileSaved] = useState(false);
  const [profileSaving, setProfileSaving] = useState(false);
  const [profileError, setProfileError] = useState('');

  const [passwordData, setPasswordData] = useState({
    current_password: '',
    password: '',
    password_confirmation: '',
  });
  const [passwordSaved, setPasswordSaved] = useState(false);
  const [passwordSaving, setPasswordSaving] = useState(false);
  const [passwordError, setPasswordError] = useState('');

  const handleProfileChange = (e) => {
    setProfile({ ...profile, [e.target.name]: e.target.value });
  };

  const handleProfileSubmit = async (e) => {
    e.preventDefault();
    setProfileError('');
    setProfileSaving(true);
    try {
      await updateProfile(profile);
      setProfileSaved(true);
      setTimeout(() => setProfileSaved(false), 3000);
    } catch (err) {
      const data = err.response?.data;
      if (data?.errors) {
        setProfileError(Object.values(data.errors).flat()[0]);
      } else {
        setProfileError('Failed to update profile.');
      }
    } finally {
      setProfileSaving(false);
    }
  };

  const handlePasswordChange = (e) => {
    setPasswordData({ ...passwordData, [e.target.name]: e.target.value });
  };

  const handlePasswordSubmit = async (e) => {
    e.preventDefault();
    setPasswordError('');
    if (passwordData.password !== passwordData.password_confirmation) {
      setPasswordError('Passwords do not match.');
      return;
    }
    setPasswordSaving(true);
    try {
      await updatePassword(passwordData);
      setPasswordSaved(true);
      setPasswordData({ current_password: '', password: '', password_confirmation: '' });
      setTimeout(() => setPasswordSaved(false), 3000);
    } catch (err) {
      const data = err.response?.data;
      if (data?.errors) {
        setPasswordError(Object.values(data.errors).flat()[0]);
      } else {
        setPasswordError('Failed to update password.');
      }
    } finally {
      setPasswordSaving(false);
    }
  };

  return (
    <div>
      <h2 className="text-xl font-bold mb-6">Account Settings</h2>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2">
          <form onSubmit={handleProfileSubmit} className="border border-gray-100 rounded-lg p-6 space-y-5">
            <h3 className="text-sm font-bold uppercase tracking-wider">Profile Information</h3>
            <div>
              <label className="text-xs text-gray-500 mb-1 block font-medium uppercase tracking-wider">Full Name</label>
              <input name="name" value={profile.name} onChange={handleProfileChange} className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900 transition" />
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="text-xs text-gray-500 mb-1 block font-medium uppercase tracking-wider">Email</label>
                <input name="email" type="email" value={profile.email} onChange={handleProfileChange} className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900 transition" />
              </div>
              <div>
                <label className="text-xs text-gray-500 mb-1 block font-medium uppercase tracking-wider">Phone</label>
                <input name="phone" type="tel" value={profile.phone} onChange={handleProfileChange} className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900 transition" />
              </div>
            </div>
            {profileError && <p className="text-xs text-red-500">{profileError}</p>}
            <div className="flex items-center gap-3">
              <button type="submit" disabled={profileSaving} className="bg-gray-900 text-white px-6 py-2.5 text-sm font-medium hover:bg-gray-800 transition uppercase tracking-wider disabled:opacity-50">
                {profileSaving ? 'Saving...' : 'Save Changes'}
              </button>
              {profileSaved && <span className="text-xs text-green-600 font-medium">Changes saved successfully!</span>}
            </div>
          </form>

          <form onSubmit={handlePasswordSubmit} className="border border-gray-100 rounded-lg p-6 mt-6 space-y-5">
            <h3 className="text-sm font-bold uppercase tracking-wider">Change Password</h3>
            <p className="text-xs text-gray-400">Update your password to keep your account secure.</p>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <input name="current_password" type="password" value={passwordData.current_password} onChange={handlePasswordChange} placeholder="Current Password" required className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900 transition" />
              <input name="password" type="password" value={passwordData.password} onChange={handlePasswordChange} placeholder="New Password" required minLength={6} className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900 transition" />
              <input name="password_confirmation" type="password" value={passwordData.password_confirmation} onChange={handlePasswordChange} placeholder="Confirm Password" required className="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900 transition" />
            </div>
            {passwordError && <p className="text-xs text-red-500">{passwordError}</p>}
            <div className="flex items-center gap-3">
              <button type="submit" disabled={passwordSaving} className="border border-gray-900 text-gray-900 px-6 py-2.5 text-sm font-medium hover:bg-gray-900 hover:text-white transition uppercase tracking-wider disabled:opacity-50">
                {passwordSaving ? 'Updating...' : 'Update Password'}
              </button>
              {passwordSaved && <span className="text-xs text-green-600 font-medium">Password updated successfully!</span>}
            </div>
          </form>
        </div>

        <div className="lg:col-span-1">
          <div className="border border-gray-100 rounded-lg p-6 text-center sticky top-28">
            <div className="w-20 h-20 rounded-full overflow-hidden mx-auto mb-4 border-2 border-gray-100 bg-gray-100 flex items-center justify-center">
              <span className="text-2xl font-bold text-gray-400">
                {user?.name?.charAt(0)?.toUpperCase() || 'U'}
              </span>
            </div>
            <h3 className="text-sm font-bold text-gray-900">{user?.name}</h3>
            <p className="text-xs text-gray-400 mt-1">{user?.email}</p>
            <button
              onClick={logout}
              className="mt-4 text-xs text-red-500 hover:text-red-600 transition flex items-center justify-center gap-1 w-full py-2 border border-red-100 hover:border-red-200"
            >
              <LogOut className="w-3.5 h-3.5" />
              Sign Out
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
