import React, { createContext, useContext, useState, useEffect } from 'react';

const ThemeContext = createContext(null);

export function ThemeProvider({ children, initialConfig, initialSlug }) {
  const [theme, setTheme] = useState(initialConfig);
  const [themeSlug, setThemeSlug] = useState(initialSlug || 'modern');

  useEffect(() => {
    if (theme?.colors) {
      Object.entries(theme.colors).forEach(([key, value]) => {
        document.documentElement.style.setProperty(`--color-${key}`, value);
      });
    }

    if (theme?.typography) {
      Object.entries(theme.typography).forEach(([key, value]) => {
        const cssVar = `--${key.replace(/_/g, '-')}`;
        document.documentElement.style.setProperty(cssVar, value);
      });
    }
  }, [theme]);

  const updateTheme = (newConfig) => {
    setTheme(newConfig);
  };

  return (
    <ThemeContext.Provider value={{ theme, themeSlug, updateTheme, setThemeSlug }}>
      {children}
    </ThemeContext.Provider>
  );
}

export function useTheme() {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
}
