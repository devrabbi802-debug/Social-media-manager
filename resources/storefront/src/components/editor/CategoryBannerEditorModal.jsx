import React, { useState } from 'react';
import { Upload } from 'lucide-react';
import SectionEditorModal from './SectionEditorModal';
import api from '../../api/client';

const defaultBanner = {
  image: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=1920&q=80',
  label: 'Limited Edition',
  title: 'Winter Collection',
  subtitle: 'Up to 50% off on jackets & coats',
  btn_text: 'Shop Collection',
  link: '/category/jackets',
};

export default function CategoryBannerEditorModal({ sectionData, onClose, onSaved }) {
  const [banner, setBanner] = useState(() => sectionData?.categoryBanner || defaultBanner);
  const [saving, setSaving] = useState(false);
  const [uploading, setUploading] = useState(false);

  const update = (field, value) => {
    setBanner((prev) => ({ ...prev, [field]: value }));
  };

  const handleImageUpload = async (file) => {
    const formData = new FormData();
    formData.append('image', file);
    setUploading(true);
    try {
      const res = await api.post('/editor/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      update('image', res.url);
    } catch (err) {
      console.error('Upload failed:', err);
    } finally {
      setUploading(false);
    }
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      const res = await api.put('/editor/sections/category-banner', banner);
      if (onSaved) onSaved(res.category_banner);
      if (sectionData?.onBannerSaved) sectionData.onBannerSaved(res.category_banner);
      onClose();
    } catch (err) {
      console.error('Save failed:', err);
    } finally {
      setSaving(false);
    }
  };

  return (
    <SectionEditorModal title="Edit Promo Banner" onClose={onClose}>
      <div className="space-y-5">
        <div>
          <label className="block text-xs font-medium text-gray-500 mb-1">Background Image</label>
          <div className="flex items-center gap-4">
            <div className="w-24 h-16 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100">
              <img src={banner.image} alt="" className="w-full h-full object-cover" />
            </div>
            <label className="flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition text-sm text-gray-600">
              <Upload className="w-4 h-4" />
              {uploading ? 'Uploading...' : 'Change Image'}
              <input
                type="file"
                accept="image/jpeg,image/png,image/webp"
                className="hidden"
                onChange={(e) => {
                  if (e.target.files[0]) handleImageUpload(e.target.files[0]);
                }}
              />
            </label>
          </div>
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="block text-xs font-medium text-gray-500 mb-1">Label</label>
            <input
              type="text"
              value={banner.label || ''}
              onChange={(e) => update('label', e.target.value)}
              className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="e.g. Limited Edition"
            />
          </div>
          <div>
            <label className="block text-xs font-medium text-gray-500 mb-1">Button Text</label>
            <input
              type="text"
              value={banner.btn_text || ''}
              onChange={(e) => update('btn_text', e.target.value)}
              className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="e.g. Shop Collection"
            />
          </div>
          <div className="col-span-2">
            <label className="block text-xs font-medium text-gray-500 mb-1">Title</label>
            <input
              type="text"
              value={banner.title || ''}
              onChange={(e) => update('title', e.target.value)}
              className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Banner title"
            />
          </div>
          <div className="col-span-2">
            <label className="block text-xs font-medium text-gray-500 mb-1">Subtitle</label>
            <input
              type="text"
              value={banner.subtitle || ''}
              onChange={(e) => update('subtitle', e.target.value)}
              className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Banner subtitle text"
            />
          </div>
          <div className="col-span-2">
            <label className="block text-xs font-medium text-gray-500 mb-1">Link URL</label>
            <input
              type="text"
              value={banner.link || ''}
              onChange={(e) => update('link', e.target.value)}
              className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="/category/jackets or https://..."
            />
          </div>
        </div>

        <div className="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
          <button
            onClick={onClose}
            className="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition"
          >
            Cancel
          </button>
          <button
            onClick={handleSave}
            disabled={saving}
            className="px-6 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition disabled:opacity-50"
          >
            {saving ? 'Saving...' : 'Save Changes'}
          </button>
        </div>
      </div>
    </SectionEditorModal>
  );
}
