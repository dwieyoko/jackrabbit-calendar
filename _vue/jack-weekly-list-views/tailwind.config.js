const colors = require('tailwindcss/colors')

module.exports = {
    darkMode: 'class',
    mode: 'jit',
    corePlugins: {
        preflight: false,
    },
    content: [
        '.src/*.vue',
        './src/**/*.vue',
    ],
}
