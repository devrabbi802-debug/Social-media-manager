/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: 'var(--color-primary, #3B82F6)',
        secondary: 'var(--color-secondary, #10B981)',
        accent: 'var(--color-accent, #F59E0B)',
        background: 'var(--color-background, #FFFFFF)',
        text: 'var(--color-text, #1F2937)',
        headerBg: 'var(--color-header_bg, #1F2937)',
        headerText: 'var(--color-header_text, #FFFFFF)',
        footerBg: 'var(--color-footer_bg, #111827)',
        surface: 'var(--color-surface, #F9FAFB)',
        border: 'var(--color-border, #E5E7EB)',
      },
      fontFamily: {
        sans: ['var(--font-family, Hind Siliguri)', 'sans-serif'],
        heading: ['var(--heading-font, Hind Siliguri)', 'sans-serif'],
      },
      borderRadius: {
        'card': 'var(--card-style, 0.5rem)',
      },
    },
  },
  plugins: [],
};