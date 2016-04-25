var $                 = require('jquery'),
    bootstrap         = require('bootstrap'),
    Confirm           = require('./components/_Confirm'),
    ChoiceDescription = require('./components/_ChoiceDescription'),
    AjaxForm          = require('./components/_AjaxForm');

require('elao-form.js');

function init(target)
{
    $('[data-collection]', target).collection();
    $('[data-toggle="tooltip"]', target).tooltip();
    $('[data-confirm]', target).each(function (key, element) { new Confirm(element); });
    $('[data-choice-description]', target).each(function (key, element) { new ChoiceDescription(element); });

    $('.clear-on-hidden-modal', target)
        .on('show.bs.modal', function (event) {
            $(event.target).removeData('bs.modal').find('.modal-content').html($(event.target).data('placeholder'));
        })
        .on('hidden.bs.modal', function (event) {
            $(event.target).removeData('bs.modal').find('.modal-content').empty();
        })
        .on('loaded.bs.modal', function (event) {
            init(event.target);
        })
    ;

    [].forEach.call(target.querySelectorAll('[data-sheet-object-form]'), function (element) { new AjaxForm(element) });
}

init(document);
