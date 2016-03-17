var $        = require('jquery'),
    Confirm  = require('./components/_Confirm'),
    CheckAll = require('./components/_CheckAll'),
    Update   = require('./components/_Update');

require('elao-form.js');

$(document).ready(function(){
    $('[data-collection]').collection();
    $('[data-confirm]').each(function (key, element) { new Confirm(element); });
    [].forEach.call(document.querySelectorAll('[data-update]'), function (element) { new Update(element); });
    [].forEach.call(document.querySelectorAll('[data-check-all]'), function (element) { new CheckAll(element); });
});
