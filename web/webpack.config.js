var Encore = require('@symfony/webpack-encore');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const dotenv = require('dotenv');
const webpack = require('webpack');

let env = dotenv.config({path: './.env.local' } ).parsed;
if (env == undefined){
    env = dotenv.config().parsed;
}

new webpack.DefinePlugin({
  'process.env.BASE_PATH': env.BASE_PATH,
  //'process.env.LINK_API': env.LINK_API,
});

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath(`${env.PUBLIC_BUILD}`)
    // public path used by the web server to access the output path
    .setPublicPath(`${env.BASE_PATH}/build`)
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    .addPlugin(new CopyWebpackPlugin([
      // copies to {output}/static
      { from: './../maquetado/dist/css', to: 'css' },
      { from: './../maquetado/dist/fonts', to: 'fonts' },
      { from: './../maquetado/dist/images', to: 'images' },
    ]))
    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', [
      './assets/js/app.js',
      './assets/js/popper.min.js',
      './assets/js/datetimepicker-4.17.45.js',
      './assets/js/file-image-3.1.3.js',
      './assets/js/main.js',
      './assets/js/ripples.js',
      './assets/js/select2-4.0.5.js',
      './assets/js/znv-material.js',
    ])

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables @babel/preset-env polyfills
    /*
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    */

    // enables Sass/SCSS support
    //.enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer require api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/admin.js')

;

module.exports = Encore.getWebpackConfig();
