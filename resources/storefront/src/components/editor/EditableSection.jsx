import React, { useState, useRef, useEffect } from 'react';
import { useEditor } from './EditorContext';
import { Edit3 } from 'lucide-react';

export default function EditableSection({ sectionType, sectionData, children, label }) {
  const { isEditorMode, openEditor } = useEditor();
  const [selected, setSelected] = useState(false);
  const [isHovered, setIsHovered] = useState(false);
  const ref = useRef(null);

  useEffect(() => {
    if (!selected) return;
    const handleClickOutside = (e) => {
      if (ref.current && !ref.current.contains(e.target)) {
        setSelected(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [selected]);

  if (!isEditorMode) return <>{children}</>;

  const showOverlay = selected || isHovered;

  return (
    <div
      ref={ref}
      className="relative cursor-pointer"
      onClick={() => setSelected((s) => !s)}
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      {showOverlay && (
        <div className="absolute top-4 right-4 z-50 flex items-center gap-2 pointer-events-none">
          <span className="text-xs text-white bg-gray-900/80 px-2 py-1 rounded pointer-events-auto">
            {label || sectionType}
          </span>
          <button
            onClick={(e) => { e.stopPropagation(); openEditor(sectionType, sectionData); }}
            className="w-9 h-9 bg-white shadow-lg rounded-full flex items-center justify-center hover:bg-gray-50 transition border border-gray-200 pointer-events-auto"
          >
            <Edit3 className="w-4 h-4 text-gray-700" />
          </button>
        </div>
      )}
      <div className={`${showOverlay ? 'ring-2 ring-blue-500 ring-offset-2 rounded' : 'hover:ring-2 hover:ring-blue-300 hover:ring-offset-1'} transition-all`}>
        {children}
      </div>
    </div>
  );
}
