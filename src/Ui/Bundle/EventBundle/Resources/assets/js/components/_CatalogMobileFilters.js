var $      = require('jquery'),
    PubSub = require('pubsub-js');

function CatalogMobileFilters (element, filterForm, catalog) {
    var data = {};
    var filterId = element.attr('data-filter-id');
    var checkedTypes = $(filterForm).find('input[name="type[]"]');
    var action = $(filterForm).attr('action');

    // Uncheck all filter checkbox except the one with the same id clicked
    for (var i = 0; i < checkedTypes.length; i++) {
        if ($(checkedTypes[i]).attr('value') !=  filterId) {
            $(checkedTypes[i]).attr('checked', false);
        }
    }

    $(catalog).find('.catalog__item').fadeTo('fast', 0.3);

    data = $(filterForm).serialize();

    history.pushState({}, '', action + '?' + data);

    // Update sheets list
    $.get(action, data, function(response) {
        $(catalog).html(response);
        PubSub.publish('dom.added', catalog.parentNode);
    });
}

module.exports = CatalogMobileFilters;
