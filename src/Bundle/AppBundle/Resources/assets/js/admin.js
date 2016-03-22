var $               = require('jquery'),
    bootstrap       = require('bootstrap'),
    Confirm         = require('./components/_Confirm'),
    CheckAll        = require('./components/_CheckAll'),
    TemplateBuilder = require('./components/_TemplateBuilder'),
    Batch           = require('./components/_Batch'),
    Update          = require('./components/_Update');

require('elao-form.js');

$(document).ready(function(){
    $('[data-collection]').collection();
    [].forEach.call(document.querySelectorAll('[data-confirm]'), function (element) { new Confirm(element); });
    [].forEach.call(document.querySelectorAll('[data-update]'), function (element) { new Update(element); });
    [].forEach.call(document.querySelectorAll('[data-check-all]'), function (element) { new CheckAll(element, element.getAttribute('data-check-all')); });
    [].forEach.call(document.querySelectorAll('[data-template-builder]'), function (element) { new TemplateBuilder(element) });
    [].forEach.call(document.querySelectorAll('[data-batch]'), function (element) { new Batch(element) });
});
