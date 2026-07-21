import React, { createContext, useContext, useState, useCallback } from 'react';

const EditorContext = createContext(null);

export function EditorProvider({ children, isEditorMode }) {
  const [activeSection, setActiveSection] = useState(null);

  const openEditor = useCallback((sectionType, sectionData) => {
    setActiveSection({ type: sectionType, data: sectionData });
  }, []);

  const closeEditor = useCallback(() => {
    setActiveSection(null);
  }, []);

  return (
    <EditorContext.Provider value={{ isEditorMode, activeSection, openEditor, closeEditor }}>
      {children}
    </EditorContext.Provider>
  );
}

export function useEditor() {
  const ctx = useContext(EditorContext);
  if (!ctx) {
    return { isEditorMode: false, activeSection: null, openEditor: () => {}, closeEditor: () => {} };
  }
  return ctx;
}
