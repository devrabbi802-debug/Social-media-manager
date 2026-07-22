import React, { useState } from 'react';
import { Plus, Trash2, Upload, GripVertical } from 'lucide-react';
import SectionEditorModal from './SectionEditorModal';
import api from '../../api/client';

export default function BannerEditorModal({ sectionData, onClose, onSaved }) {
  const [banners, setBanners] = useState(() => {
    if (sectionData?.banners?.length > 0) {
      return sectionData.banners.map((b, i) => ({ ...b, align: b.align || 'center', sort_order: b.sort_order ?? i, _key: Date.now() + i }));
    }
    return [{ title: '', subtitle: '', btn_text: '', link: '', image: null, align: 'center', sort_order: 0, is_active: true, _key: Date.now() }];
  });
  const [saving, setSaving] = useState(false);

  const addSlide = () => {
    setBanners((prev) => [
      ...prev,
      { title: '', subtitle: '', btn_text: '', link: '', image: null, align: 'center', sort_order: prev.length, is_active: true, _key: Date.now() },
    ]);
  };

  const removeSlide = (key) => {
    if (banners.length <= 1) return;
    setBanners((prev) => prev.filter((b) => b._key !== key));
  };

  const updateSlide = (key, field, value) => {
    setBanners((prev) =>
      prev.map((b) => (b._key === key ? { ...b, [field]: value } : b))
    );
  };

  const handleImageUpload = async (key, file) => {
    const formData = new FormData();
    formData.append('image', file);
    try {
      const res = await api.post('/editor/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      setBanners((prev) =>
        prev.map((b) => (b._key === key ? { ...b, image: res.url } : b))
      );
    } catch (err) {
      console.error('Upload failed:', err);
    }
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      const payload = banners.map(({ _key, ...rest }) => rest);
      const res = await api.put('/editor/sections/banners', { banners: payload });
      if (onSaved) onSaved(res.banners);
      if (sectionData?.onBannersSaved) sectionData.onBannersSaved(res.banners);
      onClose();
    } catch (err) {
      console.error('Save failed:', err);
    } finally {
      setSaving(false);
    }
  };

  return (
    <SectionEditorModal title="Edit Slider / Banner" onClose={onClose}>
      <div className="space-y-6">
        {banners.map((slide, index) => (
          <div key={slide._key} className="border border-gray-200 rounded-lg p-4 relative">
            <div className="flex items-center gap-2 mb-4 pb-2 border-b border-gray-100">
              <GripVertical className="w-4 h-4 text-gray-300" />
              <span className="text-sm font-medium text-gray-500">Slide {index + 1}</span>
              {banners.length > 1 && (
                <button
                  onClick={() => removeSlide(slide._key)}
                  className="ml-auto text-red-400 hover:text-red-600 transition"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              )}
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-medium text-gray-500 mb-1">Title</label>
                <input
                  type="text"
                  value={slide.title || ''}
                  onChange={(e) => updateSlide(slide._key, 'title', e.target.value)}
                  className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Slide title"
                />
              </div>
              <div>
                <label className="block text-xs font-medium text-gray-500 mb-1">Button Text</label>
                <input
                  type="text"
                  value={slide.btn_text || ''}
                  onChange={(e) => updateSlide(slide._key, 'btn_text', e.target.value)}
                  className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="e.g. Shop Now"
                />
              </div>
              <div className="col-span-2">
                <label className="block text-xs font-medium text-gray-500 mb-1">Subtitle</label>
                <input
                  type="text"
                  value={slide.subtitle || ''}
                  onChange={(e) => updateSlide(slide._key, 'subtitle', e.target.value)}
                  className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Slide subtitle text"
                />
              </div>
              <div className="col-span-2">
                <label className="block text-xs font-medium text-gray-500 mb-1">Link URL</label>
                <input
                  type="text"
                  value={slide.link || ''}
                  onChange={(e) => updateSlide(slide._key, 'link', e.target.value)}
                  className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="/products or https://..."
                />
              </div>
              <div className="col-span-2">
                <label className="block text-xs font-medium text-gray-500 mb-1">Text Alignment</label>
                <div className="flex items-center gap-2">
                  {['left', 'center', 'right'].map((align) => (
                    <button
                      key={align}
                      onClick={() => updateSlide(slide._key, 'align', align)}
                      className={`px-4 py-2 text-sm rounded-lg border transition ${
                        (slide.align || 'center') === align
                          ? 'bg-gray-900 text-white border-gray-900'
                          : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'
                      }`}
                    >
                      {align.charAt(0).toUpperCase() + align.slice(1)}
                    </button>
                  ))}
                </div>
              </div>
              <div className="col-span-2">
                <label className="block text-xs font-medium text-gray-500 mb-1">Image</label>
                <div className="flex items-center gap-4">
                  {slide.image && (
                    <div className="w-20 h-14 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100">
                      <img src={slide.image} alt="" className="w-full h-full object-cover" />
                    </div>
                  )}
                  <label className="flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition text-sm text-gray-600">
                    <Upload className="w-4 h-4" />
                    {slide.image ? 'Change Image' : 'Upload Image'}
                    <input
                      type="file"
                      accept="image/jpeg,image/png,image/webp"
                      className="hidden"
                      onChange={(e) => {
                        if (e.target.files[0]) handleImageUpload(slide._key, e.target.files[0]);
                      }}
                    />
                  </label>
                </div>
              </div>
            </div>
          </div>
        ))}

        <button
          onClick={addSlide}
          className="w-full flex items-center justify-center gap-2 py-3 border-2 border-dashed border-gray-200 rounded-lg text-sm text-gray-500 hover:border-blue-500 hover:text-blue-600 transition"
        >
          <Plus className="w-4 h-4" />
          Add New Slide
        </button>

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
