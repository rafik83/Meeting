var $ = require('jquery');

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

tinymce.init({
    selector: "textarea:not(.no-tiny)",
    width: '80%',
    menubar: false,
    height: 500,
    plugins: ['lists link textcolor'],
    toolbar1: "bold italic strikethrough | alignleft aligncenter alignright | bullist numlist | undo redo | removeformat",
    toolbar2: "formatselect fontsizeselect forecolor backcolor",
    toolbar_items_size: 'small',
    removed_menuitems: 'newdocument',
    font_formats: 'Default=openSans;Script=dancingScriptRegular;',
    image_advtab: true,
    relative_urls : false,
    remove_script_host : true,
    convert_urls : false,
    paste_auto_cleanup_on_paste: true,
    style_formats_merge: true,
    style_formats: [
        {title: 'Body end title', block: 'h2', classes: 'endtitle'},
        {title: 'Body offset left', block: 'p', classes: 'offset-left'}
    ]
});
