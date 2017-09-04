var $      = require('jquery'),
    PubSub = require('pubsub-js');

function CatalogMobileFilters(element, filterForm, catalog, button) {
    var data;
    var filterId     = element.attr('data-filter-id');
    var checkedTypes = $(filterForm).find('input[name="type[]"]');
    var action       = $(filterForm).attr('action');
    var content      = element.attr('data-content');
    var count        = element.attr('data-count-participant');

    $('.catalog-mobile-button').addClass('disabled');

    button.html(
        '<span>' + content + ' </span><span class="button-count total-participants">(' + count + ')' +
        '<i class="glyphicon glyphicon-chevron-down"></i></span>'
    );

    // Uncheck all filter checkbox except the one with the same id clicked
    for (var i = 0; i < checkedTypes.length; i++) {
        if (filterId === 'all') {
            $(checkedTypes[i]).attr('checked', true);
        }

        else if ($(checkedTypes[i]).attr('value') !==  filterId) {
            $(checkedTypes[i]).attr('checked', false);
        }
    }

    $(catalog).find('.catalog__item').fadeTo('fast', 0.3);

    data = $(filterForm).serialize();

    // Update sheets list
    $.get(action, data, function(response) {
        $(catalog).html(response);
        PubSub.publish('dom.added', catalog.parentNode);
        $('.catalog-mobile-button').removeClass('disabled');
    });
}

module.exports = CatalogMobileFilters;
