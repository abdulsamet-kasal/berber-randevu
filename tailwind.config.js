/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/**/*.php",
    "./public/**/*.js",
    "./src/**/*.php",
    "./templates/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        ink: "#14110E",
        coal: "#1C1813",
        surface: "#221D17",
        line: "#3A3226",
        gold: "#D4A853",
        "gold-dark": "#B8892F",
        "gold-light": "#E8C87E",
        cream: "#F3EDE2",
        muted: "#A89C88",
        primary: "#D4A853",
        "primary-dark": "#B8892F",
        "primary-light": "#E8C87E",
        background: "#14110E",
        border: "#3A3226",
        "text-primary": "#F3EDE2",
        "text-secondary": "#A89C88",
        "text-muted": "#7A7060",
        success: "#4CAF7D",
        warning: "#E0A84F",
        error: "#D95D4E",
        info: "#7FA3C7"
      },
      fontFamily: {
        sans: ['Manrope', 'system-ui', 'sans-serif'],
        display: ['"Playfair Display"', 'Georgia', 'serif']
      }
    },
  },
  plugins: [],
}
