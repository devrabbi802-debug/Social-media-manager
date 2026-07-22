import React, { useState } from 'react';
import { Plus, Trash2, GripVertical } from 'lucide-react';
import SectionEditorModal from './SectionEditorModal';
import api from '../../api/client';

export default function NoticeEditorModal({ sectionData, onClose, onSaved }) {
  const [notices, setNotices] = useState(() => {
    const items = sectionData?.notices;
    if (items && items.length > 0) return items.map((t, i) => ({ text: t, _key: Date.now() + i }));
    return [{ text: '', _key: Date.now() }];
  });
  const [saving, setSaving] = useState(false);

  const addNotice = () => {
    setNotices((prev) => [...prev, { text: '', _key: Date.now() }]);
  };

  const removeNotice = (key) => {
    if (notices.length <= 1) return;
    setNotices((prev) => prev.filter((n) => n._key !== key));
  };

  const updateNotice = (key, value) => {
    setNotices((prev) =>
      prev.map((n) => (n._key === key ? { ...n, text: value } : n))
    );
  };

  const handleSave = async () => {
    setSaving(true);
    const texts = notices.map((n) => n.text).filter((t) => t.trim());
    try {
      await api.put('/editor/sections/notices', { notices: texts });
      window.__editor_notices = texts;
      window.dispatchEvent(new Event('notices:updated'));
      if (onSaved) onSaved(texts);
      onClose();
    } catch (err) {
      console.error('Save failed:', err);
    } finally {
      setSaving(false);
    }
  };

  return (
    <SectionEditorModal title="Edit Notice Bar" onClose={onClose}>
      <div className="space-y-4">
        {notices.map((notice, index) => (
          <div key={notice._key} className="flex items-center gap-3 border border-gray-200 rounded-lg px-4 py-3">
            <GripVertical className="w-4 h-4 text-gray-300 flex-shrink-0" />
            <span className="text-xs text-gray-400 font-medium w-6 flex-shrink-0">#{index + 1}</span>
            <input
              type="text"
              value={notice.text}
              onChange={(e) => updateNotice(notice._key, e.target.value)}
              className="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Notification text..."
            />
            {notices.length > 1 && (
              <button
                onClick={() => removeNotice(notice._key)}
                className="text-red-400 hover:text-red-600 transition flex-shrink-0"
              >
                <Trash2 className="w-4 h-4" />
              </button>
            )}
          </div>
        ))}

        <button
          onClick={addNotice}
          className="w-full flex items-center justify-center gap-2 py-3 border-2 border-dashed border-gray-200 rounded-lg text-sm text-gray-500 hover:border-blue-500 hover:text-blue-600 transition"
        >
          <Plus className="w-4 h-4" />
          Add Notice
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
