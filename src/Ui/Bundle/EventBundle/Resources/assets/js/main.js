var $                     = require('jquery'),
    PubSub                = require('pubsub-js'),
    Confirm               = require('./components/_Confirm'),
    ChoiceDescription     = require('./components/_ChoiceDescription'),
    ShowPaymentInfo       = require('./components/_ShowPaymentInfo'),
    AjaxForm              = require('./components/_AjaxForm'),
    AjaxAutocomplete      = require('./components/_AjaxAutocomplete'),
    CheckAllButton        = require('./components/_CheckAllButton'),
    SelectParent          = require('./components/_SelectParent'),
    UploadPreview         = require('./components/_UploadPreview'),
    EditableTextIndicator = require('./components/_EditableTextIndicator'),
    ProductSelector       = require('./components/_ProductSelector'),
    QuantitySelector      = require('./components/_QuantitySelector'),
    CatalogSheetCard      = require('./components/_CatalogSheetCard'),
    AgendaMeet            = require('./components/_AgendaMeet'),
    ShowMore              = require('./components/_ShowMore'),
    CatalogFilters        = require('./components/_CatalogFilters'),
    AnchorFocuser         = require('./components/_AnchorFocuser'),
    PreventMultipleSubmit = require('./components/_PreventMultipleSubmit');

require('bootstrap');
require('elao-form.js');
require('intl-tel-input');
require('select2');

function init (target) {
    $('[data-collection]', target).collection();
    $('[data-toggle="tooltip"]', target).tooltip();
    $('[data-confirm]', target).each(function (key, element) { new Confirm(element); });
    $('[data-choice-description]', target).each(function (key, element) { new ChoiceDescription(element); });

    [].forEach.call(target.querySelectorAll('.select2'), function (element) {
        $(element).select2({
            language: {
                noResults: function () {
                    return $(element).data('no-results-label');
                }
            },
            allowClear: element.getAttribute('data-disallow-clear') !== 'true'
        });
    });

    [].forEach.call(target.querySelectorAll('[data-company-info-update]'), function () {
        var anchor         = window.location.hash.substring(1);
        var anchorElements = target.getElementsByName(anchor);

        if (anchor !== '' && anchor !== null && anchorElements.length > 0) {
            new AnchorFocuser(anchorElements[0], anchor);
        }
    });

    [].forEach.call(target.querySelectorAll('.telephone-intl-input'), function (element) {
        $(element).intlTelInput({
            initialCountry: $(element).data('initial-country'),
            preferredCountries: [],
            nationalMode: false
        });
    });

    $('.catalog form', target).on('submit', function (event) {
        event.preventDefault();
    });

    $('.catalog form input, .catalog form select:not([data-disable-auto-submit])', target).on('change', function () {
        var result = new CatalogFilters($(this), $('.catalog form', target), target.querySelector('.catalog'));

        if ('checkbox' === $(this).attr('type')) {
            this.checked = result;
        }
    });

    [].forEach.call(target.querySelectorAll('.catalog__item, .catalog__sheet'), function (element) {
        new CatalogSheetCard(element, document.getElementById('request-modal'));
    });

    [].forEach.call(target.querySelectorAll('.agenda .meet'), function (element) {
        new AgendaMeet(element);
    });

    $('.dropdown-menu', target).on('click', function (e) {
        e.stopPropagation();
    });

    $('.navigation__close', target).on('click', function (e) {
        $('.navigation').toggleClass('open');
    });

    $('.clear-on-hidden-modal', target)
        .on('show.bs.modal', function (event) {
            if (event.relatedTarget !== undefined && (event.relatedTarget.href !== '' || event.relatedTarget.href !== '#')) {
                $(event.target).removeData('bs.modal').find('.modal-content').html($(event.target).data('placeholder'));
            }
        })
        .on('hidden.bs.modal', function (event) {
            $(event.target).removeData('bs.modal').find('.modal-content').empty();
        })
        .on('loaded.bs.modal', function (event) {
            PubSub.publish('dom.added', event.target);
        }.bind(this))
    ;

    $('.show-modal', target).modal('show');

    [].forEach.call(target.querySelectorAll('select[data-parent]'), function (element) {
        new SelectParent(element)
    });

    [].forEach.call(target.querySelectorAll('[data-image-preview]'), function (element) {
        new UploadPreview(element, element.getAttribute('data-image-preview'))
    });

    [].forEach.call(target.querySelectorAll('[data-sheet-object-form]'), function (element) {
        new AjaxForm(element)
    });

    [].forEach.call(target.querySelectorAll('[data-confirm]'), function (element) {
        new Confirm(element);
    });

    [].forEach.call(target.querySelectorAll('[data-ajax-form]'), function (element) {
        new AjaxForm(element);
    });

    [].forEach.call(target.querySelectorAll('[data-ajax-autocomplete]'), function (element) {
        new AjaxAutocomplete(element);
    });

    [].forEach.call(target.querySelectorAll('[data-choice-description]'), function (element) {
        new ChoiceDescription(element);
    });

    [].forEach.call(target.querySelectorAll('[data-payment-info]'), function (element) {
        new ShowPaymentInfo(element);
    });

    [].forEach.call(target.querySelectorAll('[data-text-max-length-indicator]'), function (element) {
        new EditableTextIndicator(element, element.getAttribute('data-text-max-length-indicator'), element.getAttribute('data-text-max-length-translations'));
    });

    [].forEach.call(target.querySelectorAll('[data-check-all-button]'), function (element) {
        new CheckAllButton(element, element.getAttribute('data-check-all-button'), true)
    });

    [].forEach.call(target.querySelectorAll('[data-uncheck-all-button]'), function (element) {
        new CheckAllButton(element, element.getAttribute('data-uncheck-all-button'), false)
    });

    [].forEach.call(target.querySelectorAll('[data-product-selector]'), function (element) {
        new ProductSelector(element);
    });

    [].forEach.call(target.querySelectorAll('.row-quantity'), function (element) {
        new QuantitySelector(element);
    });

    [].forEach.call(target.querySelectorAll('.object--nomenclature, .object--collection.style--style-2, .object--collection.style--style-3'), function (element) {
        new ShowMore(element.querySelector('.section__content'), element.querySelector('footer'));
    });

    [].forEach.call(target.querySelectorAll('form'), function (element) {
        new PreventMultipleSubmit(element);
    });
}

PubSub.subscribe('dom.added', function (name, element) { init(element); });

init(document);
