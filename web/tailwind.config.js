/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        background: 'var(--bg)',
        'background-alt': 'var(--bg-alt)',
        surface: 'var(--surface)',
        'surface-alt': 'var(--surface-alt)',
        accent: 'var(--accent)',
        border: 'var(--border)',
        text: 'var(--text)',
        'text-muted': 'var(--text-muted)',
      }
    },
  },
  plugins: [],
}
