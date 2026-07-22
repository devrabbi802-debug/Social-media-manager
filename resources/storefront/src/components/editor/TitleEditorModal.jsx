import React, { useState } from 'react';
import SectionEditorModal from './SectionEditorModal';
import api from '../../api/client';

export default function TitleEditorModal({ sectionType, sectionData, onClose, onSaved }) {
  const currentTitle = sectionData?.title || '';
  const sectionLabel = sectionData?.sectionLabel || sectionType;
  const [title, setTitle] = useState(currentTitle);
  const [saving, setSaving] = useState(false);

  const handleSave = async () => {
    setSaving(true);
    try {
      const res = await api.put('/editor/sections/title', { section: sectionType, title });
      if (onSaved) onSaved(res.section_titles);
      if (sectionData?.onSectionTitleSaved) sectionData.onSectionTitleSaved(res.section_titles);
      onClose();
    } catch (err) {
      console.error('Save failed:', err);
    } finally {
      setSaving(false);
    }
  };

  return (
    <SectionEditorModal title={`Edit Title — ${sectionLabel}`} onClose={onClose}>
      <div className="space-y-6">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">Section Title</label>
          <input
            type="text"
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg font-bold"
            placeholder="Enter section title..."
          />
        </div>

        <div className="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
          <button onClick={onClose} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition">Cancel</button>
          <button onClick={handleSave} disabled={saving} className="px-6 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition disabled:opacity-50">
            {saving ? 'Saving...' : 'Save Changes'}
          </button>
        </div>
      </div>
    </SectionEditorModal>
  );
}