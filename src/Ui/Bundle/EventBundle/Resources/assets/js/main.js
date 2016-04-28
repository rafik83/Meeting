var $                 = require('jquery'),
    bootstrap         = require('bootstrap'),
    Confirm           = require('./components/_Confirm'),
    ChoiceDescription = require('./components/_ChoiceDescription'),
    AjaxForm          = require('./components/_AjaxForm'),
    select2           = require('select2');

require('elao-form.js');

function init(target)
{
    $('[data-collection]', target).collection();
    $('[data-toggle="tooltip"]', target).tooltip();
    $('[data-confirm]', target).each(function (key, element) { new Confirm(element); });
    $('[data-choice-description]', target).each(function (key, element) { new ChoiceDescription(element); });

    [].forEach.call(target.querySelectorAll('.select2'), function (element) {
        $(element).select2({'language': {
            'noResults': function () {
                return $(element).data('no-results-label');
            }
        }});
    });

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
