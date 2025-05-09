let Encore = require('@symfony/webpack-encore');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const url = require("url");
const fileSync = require("fs");
const mime = require("mime");

Encore
    .setOutputPath('public')
    .setPublicPath('/bundles/bifroestlogin')
    .setManifestKeyPrefix('bifroestlogin')

    .addStyleEntry('layout', './layout/css/layout.css')


    //.splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    //.enableSingleRuntimeChunk()
    .disableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enablePostCssLoader()
    .enableSassLoader()
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables @babel/preset-env polyfills
    /*.configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })*/
    .configureBabel(function(babelConfig) {
        babelConfig.plugins.push('@babel/plugin-transform-runtime');
    }, {});

let defaultConfig = Encore.getWebpackConfig();

// Enable Sass @debug
if (defaultConfig.stats.loggingDebug) {
    defaultConfig.stats.loggingDebug.push = 'sass-loader';
} else {
    defaultConfig.stats.loggingDebug = [];
    defaultConfig.stats.loggingDebug.push('sass-loader');
}

defaultConfig.name = 'default';

module.exports = [defaultConfig];