var $                 = require('jquery'),
    bootstrap         = require('bootstrap'),
    PubSub            = require('pubsub-js'),
    Confirm           = require('./components/_Confirm'),
    AjaxForm          = require('./components/_AjaxForm'),
    ChoiceDescription = require('./components/_ChoiceDescription');

require('elao-form.js');

function init (target) {
    $('[data-collection]', target).collection();
    $('[data-toggle="tooltip"]', target).tooltip();

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

    [].forEach.call(target.querySelectorAll('[data-confirm]'), function (element) { new Confirm(element); });
    [].forEach.call(target.querySelectorAll('[data-ajax-form]'), function (element) { new AjaxForm(element); });
    [].forEach.call(target.querySelectorAll('[data-choice-description]'), function (element) { new ChoiceDescription(element); });
}

PubSub.subscribe('dom.added', function (name, element) { init(element); });

init(document);
