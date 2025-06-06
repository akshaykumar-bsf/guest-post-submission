module.exports = {
  content: [
    './src/**/*.{js,jsx}',
    './public/**/*.php'
  ],
  theme: {
    extend: {
      colors: {
        primary: '#0073aa',
        'primary-dark': '#005177',
        secondary: '#23282d',
        success: '#46b450',
        danger: '#dc3232',
        warning: '#ffb900',
        info: '#00a0d2'
      },
      spacing: {
        // Custom spacing if needed
      },
      screens: {
        'sm': '640px',
        'md': '768px',
        'lg': '1024px',
        'xl': '1280px',
      }
    },
  },
  plugins: [],
}
