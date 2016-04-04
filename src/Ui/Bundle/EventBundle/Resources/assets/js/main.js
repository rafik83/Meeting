var $         = require('jquery'),
    bootstrap = require('bootstrap'),
    Confirm   = require('./components/_Confirm');

require('elao-form.js');

$(document).ready(function(){
    $('[data-collection]').collection();
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-confirm]').each(function (key, element) { new Confirm(element); });
});
