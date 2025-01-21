const mix = require('laravel-mix');

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

mix.setResourceRoot('../');
mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/scripts.js', 'public/js')
    .css('resources/css/pre-app.css', 'public/css')
    .sass('resources/sass/app.scss', 'public/css')
    .css('resources/css/app.css', 'public/css')
    .sourceMaps();
mix.copy('resources/js/scripts/vast-player.min.js', 'public/resources/vast-player.min.js');
