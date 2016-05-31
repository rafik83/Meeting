var $                 = require('jquery'),
    PubSub            = require('pubsub-js'),
    Confirm           = require('./components/_Confirm'),
    ChoiceDescription = require('./components/_ChoiceDescription'),
    AjaxForm          = require('./components/_AjaxForm'),
    SelectParent      = require('./components/_SelectParent'),
    UploadPreview     = require('./components/_UploadPreview');

require('bootstrap');
require('elao-form.js');
require('intl-tel-input');
require('select2');

function init (target) {
    $('[data-collection]', target).collection();
    $('[data-toggle="tooltip"]', target).tooltip();
    $('[data-confirm]', target).each(function (key, element) { new Confirm(element); });
    $('[data-choice-description]', target).each(function (key, element) { new ChoiceDescription(element); });

    [].forEach.call(target.querySelectorAll('.select2'), function (element) {
        $(element).select2({
            language: {
                noResults: function () {
                    return $(element).data('no-results-label');
                }
            },
            allowClear: true
        });
    });

    [].forEach.call(target.querySelectorAll('.telephone-intl-input'), function (element) {
        $(element).intlTelInput({
            initialCountry: $(element).data('initial-country'),
            preferredCountries: [],
            nationalMode: false
        });
    });

    $('.clear-on-hidden-modal', target)
        .on('show.bs.modal', function (event) {
            if (event.relatedTarget !== undefined && (event.relatedTarget.href !== '' || event.relatedTarget.href !== '#')) {
                $(event.target).removeData('bs.modal').find('.modal-content').html($(event.target).data('placeholder'));
            }
        })
        .on('hidden.bs.modal', function (event) {
            $(event.target).removeData('bs.modal').find('.modal-content').empty();
        })
        .on('loaded.bs.modal', function (event) {
            PubSub.publish('dom.added', event.target);
        }.bind(this))
    ;

    $('.show-modal').modal('show');

    [].forEach.call(target.querySelectorAll('select[data-parent]'), function (element) { new SelectParent(element) });
    [].forEach.call(target.querySelectorAll('[data-image-preview]'), function (element) { new UploadPreview(element, element.getAttribute('data-image-preview')) });
    [].forEach.call(target.querySelectorAll('[data-sheet-object-form]'), function (element) { new AjaxForm(element) });
    [].forEach.call(target.querySelectorAll('[data-confirm]'), function (element) { new Confirm(element); });
    [].forEach.call(target.querySelectorAll('[data-ajax-form]'), function (element) { new AjaxForm(element); });
    [].forEach.call(target.querySelectorAll('[data-choice-description]'), function (element) { new ChoiceDescription(element); });
}

PubSub.subscribe('dom.added', function (name, element) { init(element); });

init(document);
