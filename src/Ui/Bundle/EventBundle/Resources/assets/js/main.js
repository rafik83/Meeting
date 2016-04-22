var $         = require('jquery'),
    bootstrap = require('bootstrap'),
    Confirm   = require('./components/_Confirm');

require('elao-form.js');

$('[data-collection]').collection();
$('[data-toggle="tooltip"]').tooltip();
$('[data-confirm]').each(function (key, element) { new Confirm(element); });

$('.clear-on-hidden-modal')
    .on('show.bs.modal', function (e) {
        $(e.target).removeData('bs.modal').find('.modal-content').html($(e.target).data('placeholder'));
    })
    .on('hidden.bs.modal', function (e) {
        $(e.target).removeData('bs.modal').find('.modal-content').empty();
    })
;
