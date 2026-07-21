import React from 'react';
import { EyeOff } from 'lucide-react';

export default function EditorToolbar({ onExit }) {
  return (
    <>
    <style>{`#store-notice-bar { top: 44px !important; }`}</style>
    <div className="fixed top-0 left-0 right-0 z-[200] bg-gray-900 text-white px-4 py-2 flex items-center justify-between shadow-lg">
      <div className="flex items-center gap-3">
        <div className="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
        <span className="text-sm font-medium">Editor Mode</span>
      </div>
      <div className="flex items-center gap-2">
        <span className="text-xs text-gray-400">Hover over any section to edit</span>
        <button
          onClick={onExit}
          className="flex items-center gap-1.5 px-3 py-1.5 bg-white/10 hover:bg-white/20 rounded-lg text-sm transition"
        >
          <EyeOff className="w-4 h-4" />
          Exit Editor
        </button>
      </div>
    </div>
    </>);
}
