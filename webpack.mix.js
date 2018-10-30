let mix = require('laravel-mix');
mix.webpackConfig({
    node: {
        fs: "empty"
    }
});

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/js')
   .js('resources/assets/js/modules/bootstrap-vue.js', 'public/js')
   .js('resources/assets/js/echo_stuff.js', 'public/js')
   .js('resources/assets/js/modules/snotify.js', 'public/js')
   .js('resources/assets/js/modules/multiselect.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .version();
