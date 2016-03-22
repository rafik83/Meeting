var $               = require('jquery'),
    bootstrap       = require('bootstrap'),
    Confirm         = require('./components/_Confirm'),
    CheckAll        = require('./components/_CheckAll'),
    TemplateBuilder = require('./components/_TemplateBuilder'),
    Batch           = require('./components/_Batch'),
    Update          = require('./components/_Update');

require('elao-form.js');

// Init function

function init(target) {

    $('[data-collection]', target).collection();
    $('[data-toggle="tooltip"]', target).tooltip();

    $('.button-based-modal').on('show.bs.modal', function (event) {
        var modal  = $(this);
        var button = $(event.relatedTarget);
        var title  = button.data('modal-title');
        var body   = button.data('modal-body');

        modal.find('.modal-title').html(title);
        modal.find('.modal-body').html(title);
    });

    [].forEach.call(target.querySelectorAll('[data-confirm]'), function (element) { new Confirm(element); });
    [].forEach.call(target.querySelectorAll('[data-update]'), function (element) { new Update(element); });
    [].forEach.call(target.querySelectorAll('[data-check-all]'), function (element) { new CheckAll(element, element.getAttribute('data-check-all')); });
    [].forEach.call(target.querySelectorAll('[data-template-builder]'), function (element) { new TemplateBuilder(element) });
    [].forEach.call(target.querySelectorAll('[data-batch]'), function (element) { new Batch(element) });
}

// Call init function when element is added to DOM

document.addEventListener('dom.element.added', function (event) {
    init(event.detail.element);
});

// Init

init(document);
