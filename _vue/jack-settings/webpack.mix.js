let mix = require('laravel-mix')
let path = require('path')
let postcssImport = require('postcss-import')

mix.webpackConfig({
    stats: {
        children: true,
    },
})

// https://www.codecraftsman.us/how-to-share-vue-components-between-multiple-projects/
mix.alias({
        '@': path.join(__dirname, 'src/'),
        '@shared': path.join(__dirname, '../shared'),
    })
    .vue({version: 3})
    .sourceMaps()
    //.extract()

    .setPublicPath('./../../assets/jack-settings/')
    .js('src/main.js', 'js/main.js')
    .version()
