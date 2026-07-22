import React, { useState, useEffect } from 'react';
import { Upload, Trash2, Check, ChevronUp, ChevronDown } from 'lucide-react';
import SectionEditorModal from './SectionEditorModal';
import api from '../../api/client';

export default function AllCategoryEditorModal({ sectionData, onClose, onSaved }) {
  const [allCategories, setAllCategories] = useState([]);
  const [selected, setSelected] = useState(() => {
    if (sectionData?.allCategories?.length > 0) {
      return sectionData.allCategories.map((c, i) => ({ ...c, _key: Date.now() + i }));
    }
    return [];
  });
  const [saving, setSaving] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get('/storefront/categories').then((res) => {
      const cats = Array.isArray(res) ? res : (res.data || []);
      setAllCategories(cats);
    }).catch(() => {
      setAllCategories([]);
    }).finally(() => {
      setLoading(false);
    });
  }, []);

  const isSelected = (catId) => selected.some((s) => s.id === catId);

  const toggleCategory = (cat) => {
    if (isSelected(cat.id)) {
      setSelected((prev) => prev.filter((s) => s.id !== cat.id));
    } else {
      setSelected((prev) => [...prev, { ...cat, custom_image: null, _key: Date.now() }]);
    }
  };

  const removeSelected = (key) => {
    setSelected((prev) => prev.filter((s) => s._key !== key));
  };

  const moveUp = (index) => {
    if (index === 0) return;
    setSelected((prev) => {
      const next = [...prev];
      [next[index - 1], next[index]] = [next[index], next[index - 1]];
      return next;
    });
  };

  const moveDown = (index) => {
    if (index >= selected.length - 1) return;
    setSelected((prev) => {
      const next = [...prev];
      [next[index], next[index + 1]] = [next[index + 1], next[index]];
      return next;
    });
  };

  const handleCustomImageUpload = async (key, file) => {
    const formData = new FormData();
    formData.append('image', file);
    try {
      const res = await api.post('/editor/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      setSelected((prev) =>
        prev.map((s) => (s._key === key ? { ...s, custom_image: res.url } : s))
      );
    } catch (err) {
      console.error('Upload failed:', err);
    }
  };

  const clearCustomImage = (key) => {
    setSelected((prev) =>
      prev.map((s) => (s._key === key ? { ...s, custom_image: null } : s))
    );
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      const payload = selected.map(({ _key, ...rest }) => ({
        id: rest.id,
        name: rest.name,
        slug: rest.slug,
        image: rest.image,
        custom_image: rest.custom_image || null,
        products_count: rest.products_count || 0,
      }));
      const res = await api.put('/editor/sections/all-categories', { all_categories: payload });
      window.__editor_all_categories = res.all_categories;
      if (onSaved) onSaved(res.all_categories);
      if (sectionData?.onAllCategoriesSaved) sectionData.onAllCategoriesSaved(res.all_categories);
      onClose();
    } catch (err) {
      console.error('Save failed:', err);
    } finally {
      setSaving(false);
    }
  };

  const displayImage = (cat) => cat.custom_image || cat.image;

  return (
    <SectionEditorModal title="All Categories (Slider)" onClose={onClose}>
      <div className="space-y-6">
        <p className="text-sm text-gray-500">
          Select all categories to display in the slider. Reorder with arrows, upload custom images.
        </p>

        {loading ? (
          <div className="text-center py-8 text-sm text-gray-400">Loading categories...</div>
        ) : (
          <div className="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-60 overflow-y-auto p-1">
            {allCategories.map((cat) => {
              const sel = isSelected(cat.id);
              return (
                <button
                  key={cat.id}
                  onClick={() => toggleCategory(cat)}
                  className={`relative flex items-center gap-3 p-3 rounded-lg border text-left transition cursor-pointer ${
                    sel
                      ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500'
                      : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                  }`}
                >
                  <div className="w-10 h-10 rounded-md overflow-hidden bg-gray-100 flex-shrink-0">
                    <img src={cat.image} alt="" className="w-full h-full object-cover" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="text-sm font-medium text-gray-900 truncate">{cat.name}</div>
                    <div className="text-xs text-gray-400">{cat.products_count || 0} products</div>
                  </div>
                  {sel && (
                    <div className="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                      <Check className="w-3 h-3 text-white" />
                    </div>
                  )}
                </button>
              );
            })}
          </div>
        )}

        {selected.length > 0 && (
          <div className="space-y-3">
            <h3 className="text-sm font-semibold text-gray-700">Selected ({selected.length})</h3>
            {selected.map((cat, index) => (
              <div key={cat._key} className="border border-gray-200 rounded-lg p-4">
                <div className="flex items-center gap-3 mb-3">
                  <div className="flex flex-col items-center gap-0.5">
                    <button
                      onClick={() => moveUp(index)}
                      disabled={index === 0}
                      className="text-gray-400 hover:text-gray-700 disabled:opacity-20 disabled:cursor-not-allowed p-0.5"
                    >
                      <ChevronUp className="w-4 h-4" />
                    </button>
                    <span className="text-xs font-medium text-gray-400">{index + 1}</span>
                    <button
                      onClick={() => moveDown(index)}
                      disabled={index >= selected.length - 1}
                      className="text-gray-400 hover:text-gray-700 disabled:opacity-20 disabled:cursor-not-allowed p-0.5"
                    >
                      <ChevronDown className="w-4 h-4" />
                    </button>
                  </div>
                  <div className="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                    <img src={displayImage(cat)} alt="" className="w-full h-full object-cover" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="text-sm font-medium text-gray-900">{cat.name}</div>
                    <div className="text-xs text-gray-400">{cat.products_count || 0} products</div>
                  </div>
                  <button
                    onClick={() => removeSelected(cat._key)}
                    className="text-red-400 hover:text-red-600 transition p-1"
                  >
                    <Trash2 className="w-4 h-4" />
                  </button>
                </div>
                <div className="flex items-center gap-3">
                  {cat.custom_image && (
                    <button
                      onClick={() => clearCustomImage(cat._key)}
                      className="text-xs text-red-500 hover:text-red-700 underline"
                    >
                      Use default image
                    </button>
                  )}
                  <label className="flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition text-xs text-gray-600">
                    <Upload className="w-3.5 h-3.5" />
                    {cat.custom_image ? 'Change Image' : 'Custom Image'}
                    <input type="file" accept="image/jpeg,image/png,image/webp" className="hidden" onChange={(e) => { if (e.target.files[0]) handleCustomImageUpload(cat._key, e.target.files[0]); }} />
                  </label>
                  {cat.custom_image && <span className="text-xs text-green-600">Custom image set</span>}
                </div>
              </div>
            ))}
          </div>
        )}

        <div className="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
          <button onClick={onClose} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition">Cancel</button>
          <button onClick={handleSave} disabled={saving || selected.length === 0} className="px-6 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition disabled:opacity-50">
            {saving ? 'Saving...' : 'Save Changes'}
          </button>
        </div>
      </div>
    </SectionEditorModal>
  );
}