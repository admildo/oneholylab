/** Zara-like palette: white, black and greys only. */
module.exports = {
  content: ['./public/**/*.php', './src/views/**/*.php', './public/assets/js/*.js'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Manrope', 'Helvetica', 'Arial', 'sans-serif'],
        logo: ['Anton', 'Impact', '"Arial Narrow"', 'sans-serif'],
      },
      colors: { line: '#e3e3e3', soft: '#f3f1ed', muted: '#5c5c5c', ink: '#111111' },
    },
  },
  plugins: [],
};
