var $                       = require('jquery'),
    bootstrap               = require('bootstrap'),
    tablesort               = require('tablesort'),
    Confirm                 = require('./components/_Confirm'),
    CheckAllCheckbox        = require('./components/_CheckAllCheckbox'),
    Sortable                = require('./components/_Sortable'),
    LoadingButton           = require('./components/_LoadingButton'),
    TemplateBuilder         = require('./components/_TemplateBuilder'),
    Batch                   = require('./components/_Batch'),
    Slots                   = require('./components/_Slots'),
    SharedChoicesCollection = require('./components/_SharedChoicesCollection'),
    SortableCollection      = require('./components/_SortableCollection'),
    Update                  = require('./components/_Update');

require('elao-form.js');

// Init function

function init(target) {

    $('[data-collection]', target).collection()
        .on('collection:added', function (event, item) { init(item.element.get(0)); })
        .on('collection:deleted', function (event, item) {
            // Refresh shared choices collection in sub collections
            $('[data-shared-choices-collection]').each(function (key, element) {
                var o = $(element).data('shared-choices-collection-object');
                if (o !== undefined) {
                    o.refresh();
                }
            });

            /*
            item.element.find('.collection').each(function (key, element) {

                console.log(element);

                var collection = $(element).data('collection');

                console.log(collection); // => undefined

                // trigger collection:deleted on sub collection items
                //for (var i = 0, i < collection.items.length, i++) {
                //    collection.element.trigger('collection:deleted', [collection.items[i]]);
                //}
            });
            */

        });
    $('[data-toggle="tooltip"]', target).tooltip();
    $('[data-toggle="popover"]', target).popover();

    $('.clear-on-hidden-modal')
        .on('show.bs.modal', function (e) {
            $(e.target).removeData('bs.modal').find('.modal-content').html($(e.target).data('placeholder'));
        })
        .on('hidden.bs.modal', function (e) {
            $(e.target).removeData('bs.modal').find('.modal-content').empty();
        })
    ;

    /* tablesort */
    function cleanNumber(i) {
        return i.replace(/[^\-?0-9.]/g, '');
    }

    function compareNumber(a, b) {
        a = parseFloat(a);
        b = parseFloat(b);

        a = isNaN(a) ? 0 : a;
        b = isNaN(b) ? 0 : b;

        return a - b;
    }

    tablesort.extend('number', function(item) {
        return item.match(/^-?(\d)*-?([,\.]){0,1}-?(\d)+([E,e][\-+][\d]+)?%?$/); // Number
    }, function(a, b) {
        a = cleanNumber(a);
        b = cleanNumber(b);
        return compareNumber(b, a);
    });

    [].forEach.call(target.querySelectorAll('table.sortable'), function (element) {
        tablesort(element,  {
            descending: true
        });
    });
    /* tablesort */

    [].forEach.call(target.querySelectorAll('[data-confirm]'), function (element) { new Confirm(element); });
    [].forEach.call(target.querySelectorAll('[data-update]'), function (element) { new Update(element); });
    [].forEach.call(target.querySelectorAll('[data-check-all-checkbox]'), function (element) { new CheckAllCheckbox(element, element.getAttribute('data-check-all-checkbox')); });
    [].forEach.call(target.querySelectorAll('[data-template-builder]'), function (element) { new TemplateBuilder(element) });
    [].forEach.call(target.querySelectorAll('[data-batch]'), function (element) { new Batch(element) });
    [].forEach.call(target.querySelectorAll('[data-slot]'), function (element) { new Slots(element) });
    [].forEach.call(target.querySelectorAll('[data-sortable-collection]'), function (element) {
        new SortableCollection(element, element.getAttribute('data-sortable-collection'));
    });

    [].forEach.call(target.querySelectorAll('[data-loading-link]'), function (element) {
        var loadingButton = new LoadingButton(element, element.getAttribute('data-loading-link'));
        element.addEventListener('click', function () { loadingButton.start(); });
    });

    // Disable click on <a href="#"></a>
    [].forEach.call(target.querySelectorAll('a[href="#"'), function (element) {
        element.addEventListener('click', function (event) { event.preventDefault(); });
    });

    [].forEach.call(target.querySelectorAll('[data-shared-choices-collection]'), function (element) {
        $(element).data('shared-choices-collection-object', new SharedChoicesCollection(element, element.getAttribute('data-shared-choices-collection')));
    });

    //[].forEach.call(target.querySelectorAll('[data-shared-choices]'), function (element) {
    //    new SharedChoices(element, '[data-shared-choices="' + element.getAttribute('data-shared-choices') + '"]');
    //});
}

// Call init function when element is added to DOM

document.addEventListener('dom.element.added', function (event) {
    init(event.detail.element);
});

// Init

init(document);
