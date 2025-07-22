/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/Views/**/*.php", // Memindai semua file PHP di folder Views
    "./app/Controllers/**/*.php", // Jika Anda memiliki kelas Tailwind di controller (jarang)
    "./app/Helpers/**/*.php",   // Jika Anda memiliki kelas Tailwind di helper (jarang)
    "./public/js/**/*.js"
  ],
  theme: {
    extend: {
      fontFamily: {
        'manufacturing': ['Manufacturing Consent', 'serif'],
        poppins: ['Poppins', 'sans-serif']
      }
    }
  },
  plugins: [
    require('@tailwindcss/typography'), // Aktifkan plugin jika diinstal
    require('@tailwindcss/forms'),     // Aktifkan plugin jika diinstal
  ],
}