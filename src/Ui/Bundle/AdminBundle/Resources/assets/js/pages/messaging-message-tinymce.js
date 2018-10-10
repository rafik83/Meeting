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
    branding: false,
    selector: "textarea:not(.no-tiny)",
    width: '100%',
    menubar: false,
    height: 500,
    plugins: ['lists link textcolor colorpicker code'],
    toolbar1: "bold italic strikethrough underline | forecolor backcolor | formatselect fontsizeselect",
    toolbar2: "alignleft aligncenter alignright | bullist numlist | link code template | undo redo | removeformat",
    toolbar3: "tags links",
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
    ],
    // Add toolbar listboxes for tags and links
    setup: function (editor) {
        var placeholders = JSON.parse(editor.getElement().getAttribute('data-placeholders'));
        // Réf.: https://www.tinymce.com/docs/demo/custom-toolbar-listbox/
        editor.addButton('tags', {
            type: 'listbox',
            text: placeholders.labels.tags,
            onselect: function (e) {
                editor.insertContent(this.value());
            },
            values: placeholders.tags
        });
        editor.addButton('links', {
            type: 'listbox',
            text: placeholders.labels.links,
            onselect: function (e) {
                editor.insertContent('<a href="' + this.value() + '">' + this.text() + '</a>');
            },
            values: placeholders.links
        });
    }
});
