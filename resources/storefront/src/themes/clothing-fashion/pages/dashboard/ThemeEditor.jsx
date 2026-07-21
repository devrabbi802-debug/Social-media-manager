import React, { useState, useEffect } from 'react';
import { Monitor, ExternalLink } from 'lucide-react';
import api from '../../../../api/client';

export default function ThemeEditor() {
  const [themes, setThemes] = useState([]);
  const [activeTheme, setActiveTheme] = useState(null);
  const [editorOpen, setEditorOpen] = useState(false);
  const [mode, setMode] = useState('select');

  useEffect(() => {
    const fetchThemes = async () => {
      try {
        const all = await api.get('/themes');
        setThemes(all);
        const cfg = await api.get('/storefront/config');
        setActiveTheme(cfg?.theme?.slug || 'clothing-fashion');
      } catch (err) {
        console.error('Failed to load themes', err);
      }
    };
    fetchThemes();
  }, []);

  const openEditor = (slug) => {
    setActiveTheme(slug);
    setEditorOpen(true);
  };

  if (editorOpen) {
    return (
      <div className="fixed inset-0 z-[300] bg-white">
        <div className="absolute top-0 left-0 right-0 z-10 bg-gray-900 text-white px-4 py-2.5 flex items-center justify-between shadow-lg">
          <div className="flex items-center gap-3">
            <Monitor className="w-5 h-5" />
            <span className="text-sm font-medium">Editing: {themes.find(t => t.slug === activeTheme)?.name || activeTheme}</span>
          </div>
          <div className="flex items-center gap-2">
            <span className="text-xs text-gray-400">Hover sections to edit</span>
            <button
              onClick={() => setEditorOpen(false)}
              className="flex items-center gap-1.5 px-3 py-1.5 bg-white/10 hover:bg-white/20 rounded-lg text-sm transition"
            >
              <ExternalLink className="w-4 h-4" />
              Close Editor
            </button>
          </div>
        </div>
        <iframe
          src={`/?editor=true&theme=${activeTheme}`}
          className="w-full h-full border-0"
          style={{ marginTop: '44px' }}
          title="Theme Editor"
        />
      </div>
    );
  }

  return (
    <div>
      <div className="mb-6">
        <h2 className="text-lg font-bold text-gray-900">Theme Editor</h2>
        <p className="text-sm text-gray-400 mt-1">Choose a theme to customize your store</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {themes.map((theme) => {
          const isActive = theme.slug === activeTheme;
          return (
            <div
              key={theme.slug}
              className={`border-2 rounded-xl overflow-hidden transition ${
                isActive ? 'border-gray-900' : 'border-gray-100 hover:border-gray-300'
              }`}
            >
              <div className="aspect-video bg-gray-100 flex items-center justify-center">
                <Monitor className="w-12 h-12 text-gray-300" />
              </div>
              <div className="p-4">
                <div className="flex items-center justify-between mb-3">
                  <div>
                    <h3 className="font-bold text-gray-900">{theme.name}</h3>
                    {isActive && <span className="text-xs text-green-600 font-medium">Active</span>}
                  </div>
                </div>
                <button
                  onClick={() => openEditor(theme.slug)}
                  className="w-full flex items-center justify-center gap-2 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition"
                >
                  <Monitor className="w-4 h-4" />
                  Customize
                </button>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}
