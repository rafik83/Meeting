var Encore = require('@symfony/webpack-encore');
var webpack = require('webpack');
var CopyWebPackPlugin = require('copy-webpack-plugin');

Encore
    .disableSingleRuntimeChunk()
// directory where all compiled assets will be stored
    .setOutputPath('web/assets')

    // what's the public path to this directory (relative to your project's document root dir)
    .setPublicPath('/assets/')

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // will output as web/build/app.js
    .addEntry('js/main', './src/Ui/Bundle/EventBundle/Resources/assets/js/main.js')
    .addEntry('js/badge-scan', './src/Ui/Bundle/EventBundle/Resources/assets/js/components/badge/scan.js')
    .addEntry('js/admin', './src/Ui/Bundle/AdminBundle/Resources/assets/js/admin.js')
    .addEntry('js/chart', './src/Ui/Bundle/AdminBundle/Resources/assets/js/chart.js')
    .addEntry('js/agenda', './src/Ui/Bundle/AdminBundle/Resources/assets/js/agenda.js')
    .addEntry('js/qrcode', './src/Ui/Bundle/AdminBundle/Resources/assets/js/accessControl/qrcode.js')
    .addEntry('js/spool', './src/Ui/Bundle/AdminBundle/Resources/assets/js/accessControl/spool.js')
    .addEntry('js/checkin', './src/Ui/Bundle/AdminBundle/Resources/assets/js/accessControl/checkin.js')
    .addEntry('tinymce/tinymce', './src/Ui/Bundle/AdminBundle/Resources/assets/js/tinymce.js')
    .addEntry('tinymce/pages/messaging-message-tinymce', './src/Ui/Bundle/AdminBundle/Resources/assets/js/pages/messaging-message-tinymce.js')
    .addEntry('tinymce/tip/tip', './src/Ui/Bundle/AdminBundle/Resources/assets/js/tip/tip.js')
    .addEntry('tinymce/init-tinymce', './src/Ui/Bundle/AdminBundle/Resources/assets/js/tinymce/init-tinymce.js')

    // will output as web/build/global.css
    .addStyleEntry('css/adminStyle', './src/Ui/Bundle/AdminBundle/Resources/assets/sass/admin.scss')
    .addStyleEntry('css/mainStyle', './src/Ui/Bundle/EventBundle/Resources/assets/sass/main.scss')
    .addStyleEntry('tinymce/tinymceStyle', './src/Ui/Bundle/AdminBundle/Resources/assets/sass/tinymce.scss')

    // allow sass/scss files to be processed
    .enableSassLoader()

    // allow legacy applications to use $/jQuery as a global variable
    .autoProvidejQuery()

    .addPlugin(new CopyWebPackPlugin([
        {from: './node_modules/tinymce/skins/lightgray/', to: 'tinymce/skins/lightgray'},
        {from: './src/Ui/Bundle/AdminBundle/Resources/assets/images', to: 'images'},
        {from: './src/Ui/Bundle/EventBundle/Resources/assets/images', to: 'images'},
        {from: './src/Ui/Bundle/EventBundle/Resources/assets/fonts', to: 'fonts'},
        {from: './src/Ui/Bundle/EventBundle/Resources/assets/sounds', to: 'sounds'}
    ]))
    .addPlugin(new webpack.ProvidePlugin({
        'jQuery': 'jquery',
        'window.jQuery': 'jquery',
        'jquery': 'jquery',
        'window.jquery': 'jquery',
        '$': 'jquery',
        'window.$': 'jquery',
        // In case you imported plugins individually, you must also require them here:
        Util: "exports-loader?Util!bootstrap/js/dist/util",
        Dropdown: "exports-loader?Dropdown!bootstrap/js/dist/dropdown",
        Modal: "exports-loader?Modal!bootstrap/js/dist/model"
    }))

    .enableSourceMaps(!Encore.isProduction())
    .enableReactPreset()

    // add hash to the build files
    .enableVersioning()
;

var config = Encore.getWebpackConfig();

config.resolve = {
    alias: {
        'jquery': require.resolve('jquery')
    }
};

config.watchOptions = { poll: true };

module.exports = config;
