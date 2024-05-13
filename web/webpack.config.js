const Encore = require('@symfony/webpack-encore');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const dotenv = require('dotenv');
const webpack = require('webpack');

let env = dotenv.config({path: './.env.local' } ).parsed;
if (env === undefined){
    env = dotenv.config().parsed;
}

new webpack.DefinePlugin({
    'process.env.BASE_PATH': env.BASE_PATH,
    //'process.env.LINK_API': env.LINK_API,
});

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath(`${env.PUBLIC_BUILD}`)
    .setPublicPath(`${env.BASE_PATH}/build`)
    .setManifestKeyPrefix('build/')

    .addPlugin(new CopyWebpackPlugin({
        patterns: [
            { from: './../maquetado/dist/fonts', to: 'fonts' },
            { from: './../maquetado/dist/css', to: 'css' },
            { from: './../maquetado/dist/images', to: 'images' },
        ],
    }))

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

    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();