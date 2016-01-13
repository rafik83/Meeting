var $       = require('jquery'),
    Confirm = require('./components/_Confirm');

require('elao-form.js');

$(document).ready(function(){
    $('[data-collection]').collection();
    $('[data-confirm]').each(function (key, element) { new Confirm(element); });
});
