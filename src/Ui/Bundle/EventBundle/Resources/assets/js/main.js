var $ = require('jquery'),
    PubSub = require('pubsub-js'),
    Confirm = require('./components/_Confirm'),
    ChoiceDescription = require('./components/_ChoiceDescription'),
    ShowPaymentInfo = require('./components/_ShowPaymentInfo'),
    AjaxForm = require('./components/_AjaxForm'),
    AjaxAutocomplete = require('./components/_AjaxAutocomplete'),
    CheckAllButton = require('./components/_CheckAllButton'),
    SelectParent = require('./components/_SelectParent'),
    UploadPreview = require('./components/_UploadPreview'),
    EditableTextIndicator = require('./components/_EditableTextIndicator'),
    ProductSelector = require('./components/_ProductSelector'),
    QuantitySelector = require('./components/_QuantitySelector'),
    CatalogSheetCard = require('./components/_CatalogSheetCard'),
    Agenda = require('./components/agenda'),
    AgendaRefresh = require('./components/agenda/_Refresh'),
    AgendaAllSheet = require('./components/agenda/_AgendaAllSheet'),
    Program = require('./components/agenda/_Program'),
    ShowMore = require('./components/_ShowMore'),
    ShowMoreParticipants = require('./components/_ShowMoreParticipants'),
    CatalogFilters = require('./components/_CatalogFilters'),
    CatalogMobileFilters = require('./components/catalog/_CatalogMobileFilters'),
    MeetingRequestMobileFilters = require('./components/MeetingRequest/_MeetingRequestMobileFilters'),
    AnchorFocuser = require('./components/_AnchorFocuser'),
    Happening = require('./components/_Happening'),
    ToggleVisibility = require('./components/_ToggleVisibility'),
    PreventMultipleSubmit = require('./components/_PreventMultipleSubmit'),
    FilterRequestByType = require('./components/MeetingRequest/_FilterByType'),
    CatalogPagination = require('./components/_CatalogPagination'),
    IgnorePhoneConfirmation = require('./components/agenda/_IgnorePhoneConfirmation'),
    PackageParticipantProducts = require('./components/_PackageParticipantProducts'),
    CatalogSelectFromNomenclaturesField = require('./components/_CatalogSelectFromNomenclaturesField'),
    SortParticipants = require('./components/_SortParticipants'),
    DateTimePicker = require('../../../../../../../assets/js/components/DateTimePicker'),
    addSubmitEventListenerOnElementChange = require('./components/form/_AddSubmitEventListenerOnElementChange')
;

require('bootstrap');
require('elao-form.js');
require('intl-tel-input');
require('select2');
require('@babel/polyfill');

function init (target) {
    // always first one in order to avoid collision
    [].forEach.call(target.querySelectorAll('.select2'), function (element) {
        $(element).select2({
            language: {
                noResults: function () {
                    return $(element).data('no-results-label');
                }
            },
            allowClear: element.getAttribute('data-disallow-clear') !== 'true',
            minimumResultsForSearch: 5
        });
    });

    $('[data-collection]', target)
        .collection()
        .on('collection:added', function (event, item) { init(item.element.get(0)); });

    $('[data-toggle="tooltip"]', target).tooltip();
    $('[data-choice-description]', target).each(function (key, element) { new ChoiceDescription(element); });

    [].forEach.call($('[data-datatimepicker]'), function (element) {
        new DateTimePicker(element);
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

    [].forEach.call(target.querySelectorAll('[data-catalog-mobile-menu]'), function (element) {
        new CatalogMobileFilters(
            document.querySelector('.catalog-mobile-menu'),
            element,
            target.querySelector('.catalog form')
        );
    });

    [].forEach.call(target.querySelectorAll('[data-meeting-request-mobile-menu]'), function (element) {
        new MeetingRequestMobileFilters(
            document.querySelector('.catalog-mobile-menu'),
            element,
            target.querySelector('.catalog form')
        );
    });

    [].forEach.call(target.querySelectorAll('.catalog__item, .catalog__sheet'), function (element) {
        new CatalogSheetCard(element, document.getElementById('request-modal'));
    });

    [].forEach.call(target.querySelectorAll('[data-select-from-nomenclature-field]'), function (element) {
        new CatalogSelectFromNomenclaturesField(element, document.getElementById('select-from-nomenclatures-modal'));
    });


    const agendaElement = document.getElementById('agenda');

    if (agendaElement) {
        Agenda.init(target, agendaElement);
    }

    const agendaAllSheetElement = document.getElementById('agendaAllSheet');

    if (agendaAllSheetElement) {
        new AgendaAllSheet(agendaAllSheetElement);
    }

    [].forEach.call(target.querySelectorAll('.program-happening, .program-mass'), function(element) {
       new Program(element);
    });

    [].forEach.call(target.querySelectorAll('.catalog__meeting_request'), function (element) {
        var buttons = target.querySelector('[data-meeting-request-filter-type-buttons]');

        if (buttons !== null) {
            new FilterRequestByType(element, buttons);
        }
    });

    $('.dropdown-menu', target).on('click', function (e) {
        e.stopPropagation();
    });

    $('.navigation .navigation__close', target).on('click', function (e) {
        $('.navigation').toggleClass('open');
    });

    $('#navigation-mobile, .mobile-menu .navigation__close', target).on('click', function (e) {
        $('.mobile-menu').toggle();
        setTimeout(function() {
          $('body').toggleClass('menu-mobile-opened').scrollTop(0);
        }, 1);
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

    [].forEach.call(target.querySelectorAll('.sheet-participants-list-users'), function (element) {
        new ShowMoreParticipants(element);
    });

    [].forEach.call(target.querySelectorAll('.object--nomenclature, .object--collection.style--style-2, .object--collection.style--style-3'), function (element) {
        var showMoreElement = element.querySelector('.section__content');
        var buttonContainer = element.querySelector('footer');

        if (showMoreElement !== null && buttonContainer !== null) {
            new ShowMore(showMoreElement, buttonContainer);
        }
    });

    [].forEach.call(target.querySelectorAll('.happening'), function (element) {
        new Happening(element, document.getElementById('happening-modal'));
    });

    [].forEach.call(target.querySelectorAll('form'), function (element) {
        new PreventMultipleSubmit(element);
    });

    [].forEach.call(target.querySelectorAll('[data-page]'), function (element) {
        new CatalogPagination(element);
    });

    [].forEach.call(target.querySelectorAll('[data-ajax-autocomplete]'), function (element) {
        new AjaxAutocomplete(element);
    });

    [].forEach.call(target.querySelectorAll('[data-ignore-phone-confirmation-url]'), function (element) {
        new IgnorePhoneConfirmation(element);
    });

    [].forEach.call(target.querySelectorAll('[data-serialized-participant-products]'), function (element) {
        new PackageParticipantProducts(element);
    });

    [].forEach.call(target.querySelectorAll('[data-toggle-visibility]'), function (element) {
        new ToggleVisibility(element);
    });

    addSubmitEventListenerOnElementChange(target, 'evaluation', 'evaluation');
    addSubmitEventListenerOnElementChange(target, 'evaluate_meeting', 'evaluation');

    if (target.querySelector('[data-agenda-autorefresh]')) {
        new AgendaRefresh();
    }

    [].forEach.call(target.querySelectorAll('.sort-participants'), function (element) {
        new SortParticipants(element);
    });
}

PubSub.subscribe('dom.added', function (name, element) { init(element); });

PubSub.subscribe('build.select2', function (name, target) {
    [].forEach.call(target.querySelectorAll('[data-ajax-autocomplete-without-auto-build]'), function (element) {
        new AjaxAutocomplete(element);
    });
});

init(document);
