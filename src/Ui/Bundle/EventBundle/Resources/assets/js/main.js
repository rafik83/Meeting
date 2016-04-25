var $               = require('jquery'),
    bootstrap       = require('bootstrap'),
    PubSub          = require('pubsub-js'),
    Confirm         = require('./components/_Confirm'),
    TypeDescription = require('./components/_TypeDescription'),
    AjaxForm        = require('./components/_AjaxForm');

require('elao-form.js');

function init (target) {
    $('[data-collection]', target).collection();
    $('[data-toggle="tooltip"]', target).tooltip();
    $('[data-confirm]', target).each(function (key, element) { new Confirm(element); });

    $('.clear-on-hidden-modal', target)
        .on('show.bs.modal', function (event) {
            $(event.target).removeData('bs.modal').find('.modal-content').html($(event.target).data('placeholder'));
        })
        .on('hidden.bs.modal', function (event) {
            $(event.target).removeData('bs.modal').find('.modal-content').empty();
        })
        .on('loaded.bs.modal', function (event) {
            PubSub.publish('dom.added', event.target);
        }.bind(this))
    ;

    [].forEach.call(target.querySelectorAll('[data-ajax-form]'), function (element) { new AjaxForm(element) });
    [].forEach.call(target.querySelectorAll('input[type=radio][data-description]'), function (element) { new TypeDescription(element); });
}

PubSub.subscribe('dom.added', function (name, element) { init(element); });

init(document);
