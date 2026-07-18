/**
 * Theme utility functions
 */

/**
 * Merge preset theme config with tenant overrides
 */
export function mergeThemeConfig(preset, overrides = {}) {
  if (!preset) return {};

  const config = { ...preset };

  if (overrides.colors) {
    config.colors = { ...config.colors, ...overrides.colors };
  }

  if (overrides.typography) {
    config.typography = { ...config.typography, ...overrides.typography };
  }

  if (overrides.components) {
    config.components = { ...config.components, ...overrides.components };
  }

  return config;
}

/**
 * Convert theme config to CSS variables
 */
export function themeToCssVars(config) {
  const vars = {};

  if (config.colors) {
    Object.entries(config.colors).forEach(([key, value]) => {
      vars[`--color-${key}`] = value;
    });
  }

  if (config.typography) {
    Object.entries(config.typography).forEach(([key, value]) => {
      const cssVar = `--${key.replace(/_/g, '-')}`;
      vars[cssVar] = value;
    });
  }

  return vars;
}

/**
 * Apply CSS variables to document
 */
export function applyCssVars(vars) {
  Object.entries(vars).forEach(([key, value]) => {
    document.documentElement.style.setProperty(key, value);
  });
}