var $ = require('jquery');
require('elao-form.js');

// TinyMCE
tinymce.on('addEditor', function(e){
    var intervalId = setInterval(function() {
        var editor = e.editor;

        if (editor.contentAreaContainer) {
            clearInterval(intervalId);

            editor.on('change', function () {
                editor.save();
                $(editor.getElement()).trigger('change');
            });

            var $area = $(editor.contentAreaContainer);
            var $row = $area.closest('.row');
            var $ref = $('.reference > div', $row);

            $ref.addClass('mce-content-body');

            if ($area.height() > $ref.height()) {
                $ref.height($area.height());
            } else {
                editor.theme.resizeTo(null, $ref.height());
            }

            // remove required validator on hidden field
            $(editor.getElement()).removeAttr('required');
        }
    }, 100);
});

tinymceInit = () => {
    tinymce.init({
        branding: false,
        selector: '.tinymce',
        width: '100%',
        menubar: false,
        height: 200,
        plugins: ['lists link textcolor colorpicker code'],
        toolbar1: "bold italic strikethrough underline | forecolor backcolor | formatselect fontsizeselect",
        toolbar2: "alignleft aligncenter alignright | bullist numlist | link code template | undo redo | removeformat",
        toolbar_items_size: 'small',
        removed_menuitems: 'newdocument',
        font_formats: 'Default=openSans;Script=dancingScriptRegular;',
        image_advtab: true,
        relative_urls: false,
        remove_script_host: true,
        convert_urls: false,
        paste_auto_cleanup_on_paste: true,
        style_formats_merge: true,
        style_formats: [
            {title: 'Body end title', block: 'h2', classes: 'endtitle'},
            {title: 'Body offset left', block: 'p', classes: 'offset-left'}
        ],
    });
};

tinymceInit();
