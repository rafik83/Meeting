var $ = require('jquery'),
    Confirm = require('./components/_Confirm'),
    CheckAllCheckbox = require('./components/_CheckAllCheckbox'),
    LoadingButton = require('./components/_LoadingButton'),
    TemplateBuilder = require('./components/_TemplateBuilder'),
    RegistrationTemplateBuilder = require('./components/_RegistrationTemplateBuilder'),
    PrintTemplateBuilder = require('./components/_PrintTemplateBuilder'),
    Batch = require('./components/_Batch'),
    Slots = require('./components/_Slots'),
    SharedChoicesCollection = require('./components/_SharedChoicesCollection'),
    SortableCollection = require('./components/_SortableCollection'),
    Update = require('./components/_Update'),
    PreventMultipleSubmit = require('./components/_PreventMultipleSubmit'),
    AnchorFocuser = require('./components/_AnchorFocuser'),
    DateTimePicker = require('./components/_DateTimePicker'),
    MessagingMessagePreview = require('./components/_MessagingMessagePreview'),
    ParticipantVisio = require('./components/_ParticipantVisio'),
    TipPreview = require('./components/_TipPreview'),
    ToggleVisibility = require('./components/_ToggleVisibility');

require('bootstrap');
require('elao-form.js');
require('select2');
require('moment/locale/fr');
require('moment/locale/en-gb');
require('./vendor/bootstrap-duallistbox/_jquery.bootstrap-duallistbox');
require('./zendesk/zendesk');

// Init function

function init(target) {

    $('[data-collection]', target).collection()
        .on('collection:added', function (event, item) { init(item.element.get(0)); })
        .on('collection:deleted', function (event, item) {
            // Refresh shared choices collection in sub collections
            $('[data-shared-choices-collection]').each(function (key, element) {
                var o = $(element).data('shared-choices-collection-object');
                if (o !== undefined) {
                    o.refresh();
                }
            });
        });
    $('[data-toggle="tooltip"]', target).tooltip();
    $('[data-toggle="popover"]', target).popover();

    $('.clear-on-hidden-modal', target)
        .on('show.bs.modal', function (e) {
            $(e.target).removeData('bs.modal').find('.modal-content').html($(e.target).data('placeholder'));
        })
        .on('hidden.bs.modal', function (e) {
            $(e.target).removeData('bs.modal').find('.modal-content').empty();
        })
    ;

    [].forEach.call(target.querySelectorAll('.select2'), function (element) {
        $(element).select2({
            language: {
                noResults: function () {
                    return $(element).data('no-results-label');
                }
            },
            allowClear: element.getAttribute('data-placeholder') !== null
        });
    });

    [].forEach.call(target.querySelectorAll('.template-builder-body .select2-builder'), function (element) {
        $(element).select2({
            language: {
                noResults: function () {
                    return $(element).data('no-results-label');
                }
            },
            allowClear: element.getAttribute('data-placeholder') !== null
        });
    });

    [].forEach.call($('[data-datatimepicker]'), function (element) {
        new DateTimePicker(element);
    });

    [].forEach.call(target.querySelectorAll('[data-confirm]'), function (element) { new Confirm(element); });
    [].forEach.call(target.querySelectorAll('[data-update]'), function (element) { new Update(element); });
    [].forEach.call(target.querySelectorAll('[data-check-all-checkbox]'), function (element) { new CheckAllCheckbox(element, element.getAttribute('data-check-all-checkbox')); });
    [].forEach.call(target.querySelectorAll('[data-template-builder]'), function (element) { new TemplateBuilder(element) });

    [].forEach.call(target.querySelectorAll('[data-registration-template-builder]'), function (element) {
        new RegistrationTemplateBuilder(element)
    });

    [].forEach.call(target.querySelectorAll('[data-print-template-builder]'), function (element) { new PrintTemplateBuilder(element) });
    [].forEach.call(target.querySelectorAll('[data-batch]'), function (element) { new Batch(element) });
    [].forEach.call(target.querySelectorAll('[data-slot]'), function (element) { new Slots(element) });
    [].forEach.call(target.querySelectorAll('[data-sortable-collection]'), function (element) {
        new SortableCollection(element, element.getAttribute('data-sortable-collection'));
    });

    [].forEach.call(target.querySelectorAll('[data-loading-link]'), function (element) {
        var loadingButton = new LoadingButton(element, element.getAttribute('data-loading-link'));
        element.addEventListener('click', function () { loadingButton.start(); });
    });

    // Disable click on <a href="#"></a>
    [].forEach.call(target.querySelectorAll('a[href="#"'), function (element) {
        element.addEventListener('click', function (event) { event.preventDefault(); });
    });

    // Disable click on active button
    [].forEach.call(target.querySelectorAll('button.active'), function  (element) {
        element.addEventListener('click', function (event) { event.preventDefault(); });
    });

    [].forEach.call(target.querySelectorAll('[data-shared-choices-collection]'), function (element) {
        $(element).data('shared-choices-collection-object', new SharedChoicesCollection(element, element.getAttribute('data-shared-choices-collection')));
    });

    // Prevent multiple submit on input type submit
    [].forEach.call(target.querySelectorAll('form'), function (element) { new PreventMultipleSubmit(element); });


    [].forEach.call(target.querySelectorAll('[data-dual-list-box]'), function (element) {
        var selectedListLabel = element.getAttribute('data-dual-list-box-selectedListLabel');
        var nonSelectedListLabel = element.getAttribute('data-dual-list-box-nonSelectedListLabel');

        $(element).bootstrapDualListbox({
            infoText: false,
            selectorMinimalHeight: 300,
            selectedListLabel: selectedListLabel,
            nonSelectedListLabel: nonSelectedListLabel
        });
    });

    [].forEach.call(target.querySelectorAll('[data-switch-to-tab]'), function (element) {
        new AnchorFocuser(element, location);
    });

    [].forEach.call(target.querySelectorAll('[data-message-preview]'), function (element) {
        new MessagingMessagePreview(element, target.querySelector('#message_preview_iframe'), target.querySelector('#no_preview_text'));
    });

    [].forEach.call(target.querySelectorAll('.form-participant-visio'), function (element) {
        new ParticipantVisio(element);
    });

    [].forEach.call(target.querySelectorAll('[data-preview-tip]'), function (element) {
        new TipPreview(element, target.querySelector('#tip_preview'), target.querySelector('#tip_pages'));
    });

    [].forEach.call(target.querySelectorAll('[data-toggle-visibility]'), function (element) {
        new ToggleVisibility(element, target);
    })
}

// Call init function when element is added to DOM

document.addEventListener('dom.element.added', function (event) {
    init(event.detail.element);
});

// Init

init(document);
