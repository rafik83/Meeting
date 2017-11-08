var mix = require('laravel-mix');

mix
    .setResourceRoot('/assets/')
    .setPublicPath('web/assets')
    .sourceMaps()
    .options({
        clearConsole: false
    })
    .autoload({
        'jquery': ['jQuery']
    })
    // Bundle sass
    .sass('src/Ui/Bundle/AdminBundle/Resources/assets/sass/admin.scss', 'web/assets/css')
    .sass('src/Ui/Bundle/EventBundle/Resources/assets/sass/main.scss', 'web/assets/css')
    // Bundle js
    .js('src/Ui/Bundle/AdminBundle/Resources/assets/js/admin.js', 'web/assets/js')
    .js('src/Ui/Bundle/EventBundle/Resources/assets/js/main.js', 'web/assets/js')
    // GDR js
    .js('src/Ui/Bundle/AdminBundle/Resources/assets/js/agenda.js', 'web/assets/js')
    // TinyMce config
    .sass('src/Ui/Bundle/AdminBundle/Resources/assets/sass/tinymce.scss', 'web/assets/tinymce')
    .js('src/Ui/Bundle/AdminBundle/Resources/assets/js/tinymce.js', 'web/assets/tinymce')
    .js('src/Ui/Bundle/AdminBundle/Resources/assets/js/pages/messaging-message-tinymce.js', 'web/assets/tinymce/pages')
    .js('src/Ui/Bundle/AdminBundle/Resources/assets/js/tip/tip.js', 'web/assets/tinymce/tip')
    .copyDirectory('node_modules/tinymce/skins/lightgray/', 'web/assets/tinymce/skins/lightgray')
    // Images config for default user images
    .copyDirectory('src/Ui/Bundle/EventBundle/Resources/assets/images', 'web/assets/images')
;
