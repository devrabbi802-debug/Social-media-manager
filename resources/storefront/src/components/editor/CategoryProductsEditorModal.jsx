import React, { useState, useEffect } from 'react';
import { Plus, Trash2, Upload, ChevronDown } from 'lucide-react';
import SectionEditorModal from './SectionEditorModal';
import api from '../../api/client';

export default function CategoryProductsEditorModal({ sectionData, onClose, onSaved }) {
  const current = sectionData?.categoryProductsData || {};
  const [title, setTitle] = useState(current.title || 'Jackets Collection');
  const [categories, setCategories] = useState(current.categories || []);
  const [allCategories, setAllCategories] = useState([]);
  const [saving, setSaving] = useState(false);
  const [uploadingIdx, setUploadingIdx] = useState(null);

  useEffect(() => {
    api.get('/storefront/categories').then(setAllCategories).catch(() => {});
  }, []);

  const addCategory = () => {
    setCategories((prev) => [...prev, { id: null, name: '', slug: '', banner_image: '', product_count: 4 }]);
  };

  const removeCategory = (idx) => {
    setCategories((prev) => prev.filter((_, i) => i !== idx));
  };

  const updateCategory = (idx, field, value) => {
    setCategories((prev) => {
      const next = [...prev];
      next[idx] = { ...next[idx], [field]: value };
      return next;
    });
  };

  const handleCategorySelect = (idx, catId) => {
    const cat = allCategories.find((c) => c.id === parseInt(catId));
    if (cat) {
      setCategories((prev) => {
        const next = [...prev];
        next[idx] = { ...next[idx], id: cat.id, name: cat.name, slug: cat.slug };
        return next;
      });
    }
  };

  const handleImageUpload = async (idx, file) => {
    const formData = new FormData();
    formData.append('image', file);
    setUploadingIdx(idx);
    try {
      const res = await api.post('/editor/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      updateCategory(idx, 'banner_image', res.url);
    } catch (err) {
      console.error('Upload failed:', err);
    } finally {
      setUploadingIdx(null);
    }
  };

  const handleSave = async () => {
    const payload = {
      title,
      categories: categories.map((c) => ({
        id: c.id,
        name: c.name,
        slug: c.slug,
        banner_image: c.banner_image || '',
        product_count: parseInt(c.product_count) || 4,
      })),
    };

    setSaving(true);
    try {
      const res = await api.put('/editor/sections/category-products', payload);
      if (onSaved) onSaved(res.category_products);
      if (sectionData?.onCategoryProductsSaved) sectionData.onCategoryProductsSaved(res.category_products);
      onClose();
    } catch (err) {
      console.error('Save failed:', err);
    } finally {
      setSaving(false);
    }
  };

  const usedCategoryIds = categories.map((c) => c.id).filter(Boolean);

  return (
    <SectionEditorModal title="Edit Category Products" onClose={onClose}>
      <div className="space-y-6">
        <div>
          <label className="block text-xs font-medium text-gray-500 mb-1">Section Title</label>
          <input
            type="text"
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="e.g. Jackets Collection"
          />
        </div>

        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <label className="text-xs font-medium text-gray-500">Categories</label>
            <button
              onClick={addCategory}
              className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition"
            >
              <Plus className="w-3.5 h-3.5" />
              Add Category
            </button>
          </div>

          {categories.length === 0 && (
            <p className="text-sm text-gray-400 text-center py-6">No categories selected. Click "Add Category" to begin.</p>
          )}

          {categories.map((cat, idx) => (
            <div key={idx} className="border border-gray-200 rounded-lg p-4 space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-xs font-semibold text-gray-700 uppercase tracking-wider">Category {idx + 1}</span>
                <button
                  onClick={() => removeCategory(idx)}
                  className="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>

              <div>
                <label className="block text-xs font-medium text-gray-500 mb-1">Category</label>
                <div className="relative">
                  <select
                    value={cat.id || ''}
                    onChange={(e) => handleCategorySelect(idx, e.target.value)}
                    className="w-full appearance-none border border-gray-200 rounded-lg px-3 py-2 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                  >
                    <option value="">Select a category...</option>
                    {allCategories.map((ac) => (
                      <option key={ac.id} value={ac.id} disabled={usedCategoryIds.includes(ac.id) && ac.id !== cat.id}>
                        {ac.name} ({ac.products_count} products)
                      </option>
                    ))}
                  </select>
                  <ChevronDown className="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                </div>
              </div>

              <div>
                <label className="block text-xs font-medium text-gray-500 mb-1">Banner Image</label>
                <div className="flex items-center gap-4">
                  {cat.banner_image ? (
                    <div className="w-20 h-14 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100">
                      <img src={cat.banner_image} alt="" className="w-full h-full object-cover" />
                    </div>
                  ) : (
                    <div className="w-20 h-14 rounded-lg flex-shrink-0 bg-gray-100 flex items-center justify-center text-gray-300">
                      <Upload className="w-5 h-5" />
                    </div>
                  )}
                  <label className="flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition text-xs text-gray-600">
                    {uploadingIdx === idx ? 'Uploading...' : 'Upload Image'}
                    <input
                      type="file"
                      accept="image/jpeg,image/png,image/webp"
                      className="hidden"
                      onChange={(e) => {
                        if (e.target.files[0]) handleImageUpload(idx, e.target.files[0]);
                      }}
                    />
                  </label>
                  {cat.banner_image && (
                    <button
                      onClick={() => updateCategory(idx, 'banner_image', '')}
                      className="text-xs text-gray-400 hover:text-red-500 transition"
                    >
                      Remove
                    </button>
                  )}
                </div>
              </div>

              <div>
                <label className="block text-xs font-medium text-gray-500 mb-1">Products to Show</label>
                <input
                  type="number"
                  min="1"
                  max="50"
                  value={cat.product_count}
                  onChange={(e) => updateCategory(idx, 'product_count', e.target.value)}
                  className="w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>
          ))}
        </div>

        <div className="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
          <button onClick={onClose} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition">
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
