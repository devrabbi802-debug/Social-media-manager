const themeModules = {
  'clothing-fashion': () => import('./clothing-fashion'),
  classic: () => import('./classic'),
};

let currentThemeCache = null;

export async function loadTheme(slug) {
  const loader = themeModules[slug];
  if (!loader) {
    return loadTheme('clothing-fashion');
  }
  currentThemeCache = await loader();
  return currentThemeCache;
}

export function getCurrentTheme() {
  return currentThemeCache;
}

export function getAvailableThemes() {
  return Object.keys(themeModules);
}
