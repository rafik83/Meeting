var Encore = require('@symfony/webpack-encore');
var CopyWebPackPlugin = require('copy-webpack-plugin');

Encore
// directory where all compiled assets will be stored
    .setOutputPath('web/assets')

    // what's the public path to this directory (relative to your project's document root dir)
    .setPublicPath('/assets/')

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // will output as web/build/app.js
    .addEntry('js/main', './src/Ui/Bundle/EventBundle/Resources/assets/js/main.js')
    .addEntry('js/admin', './src/Ui/Bundle/AdminBundle/Resources/assets/js/admin.js')
    .addEntry('js/agenda', './src/Ui/Bundle/AdminBundle/Resources/assets/js/agenda.js')
    .addEntry('tinymce/tinymce', './src/Ui/Bundle/AdminBundle/Resources/assets/js/tinymce.js')
    .addEntry('tinymce/tinymce/pages', './src/Ui/Bundle/AdminBundle/Resources/assets/js/pages/messaging-message-tinymce.js')
    .addEntry('tinymce/tinymce/tip', './src/Ui/Bundle/AdminBundle/Resources/assets/js/tip/tip.js')

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
        {from: './src/Ui/Bundle/EventBundle/Resources/assets/images', to: 'images'}
    ]))

    .enableSourceMaps(!Encore.isProduction())
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
