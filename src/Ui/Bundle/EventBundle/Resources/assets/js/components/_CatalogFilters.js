var $      = require('jquery'),
    PubSub = require('pubsub-js');

function CatalogFilters(field, filterForm, catalog)
{
    if ('checkbox' === field.attr('type')) {
        var checked = $(filterForm).find('input[name="'+field.attr('name')+'"]:not([disabled]):checked');

        if (0 === checked.length) {
            alert($(field).closest('ul').data('message-at-least-one-checked'));

            return true;
        }
    }

    var action = $(filterForm).attr('action');
    var data = $(filterForm).serialize();

    $(catalog).find('.catalog__item').fadeTo('fast', 0.3);
    $(filterForm).find('input, select').attr('disabled','disabled');

    history.pushState({}, '', action + '?' + data);

    // Update sheets list
    $.get(action, data, function(response) {
        $(catalog).html(response);
        PubSub.publish('dom.added', catalog.parentNode);
    });

    return false;
}

module.exports = CatalogFilters;
