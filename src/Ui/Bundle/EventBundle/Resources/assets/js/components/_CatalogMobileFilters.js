var $      = require('jquery'),
    PubSub = require('pubsub-js');

function CatalogMobileFilters (element, filterForm, catalog) {
    var filterId = element.attr('data-filter-id');
    var checkedTypes = $(filterForm).find('input[name="type[]"]');
    var action = $(filterForm).attr('action');
    var data = $(filterForm).serialize();

    // Uncheck all filter checkbox except the one with the same id clicked
    for (var i = 0; i < checkedTypes.length; i++) {
        if ($(checkedTypes[i]).attr('value') !=  filterId) {
            $(checkedTypes[i]).attr('checked', false);
        }
    }

    $(catalog).find('.catalog__item').fadeTo('fast', 0.3);

    history.pushState({}, '', action + '?' + data);

    // Update sheets list
    $.get(action, data, function(response) {
        $(catalog).html(response);
        PubSub.publish('dom.added', catalog.parentNode);
    });
}

module.exports = CatalogMobileFilters;
