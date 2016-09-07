var $      = require('jquery'),
    PubSub = require('pubsub-js');

function CatalogFilters(filterForm, catalog, target)
{
    $(catalog).find('.catalog__item').fadeTo('fast', 0.3);

    var action = $(filterForm).attr('action');
    var data = $(filterForm).serialize();

    $(catalog).find('input').attr('disabled','disabled');

    history.pushState({}, '', action + '?' + data);

    // Update sheets list
    $.get(action, data, function(response) {
        $(catalog).html(response);
        PubSub.publish('dom.added', target);
    });
}

module.exports = CatalogFilters;
