const mix = require('laravel-mix');
const webpack = require("webpack");
const path = require('path');
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */
 const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
 const WorkboxPlugin = require('workbox-webpack-plugin');
 const VL = require('vuetify-loader/lib/plugin');

 mix.js('resources/js/app.js', 'public/js').vue({ version: 2 })
    .js('resources/installer/js/app.js', 'public/installer/js').vue({ version: 2 })
    .sass('resources/sass/app.scss', 'public/css')
    // .sass('resources/sass/installer/app.scss', 'public/installer/css')
 .webpackConfig({
     resolve: {
         extensions: [".*",".wasm",".mjs",".js",".jsx",".json",".vue",".*"],
         alias: {
             "@mixins": path.resolve(__dirname, './resources/js/mixins')
         }
     },
     plugins: [
        //  new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
         new VL(),
         new WorkboxPlugin.GenerateSW({
             maximumFileSizeToCacheInBytes: 100000000
         })
     ],
     output: {
         chunkFilename: 'js/chunks/[chunkhash].js',//replace with your path
     }
 })
 // .browserSync("muzzie.com");;
