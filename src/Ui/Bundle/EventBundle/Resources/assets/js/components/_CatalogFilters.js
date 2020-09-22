import $ from 'jquery';
import PubSub from 'pubsub-js';

function CatalogFilters(field, filterForm, catalog)
{
    if ('checkbox' === field.attr('type')) {
        var checked = $(filterForm).find('input[name="' + field.attr('name') + '"]:not(.disabled):checked');

        var atLeastOnChecked = $(field).closest('ul').data('message-at-least-one-checked');

        if (atLeastOnChecked !== undefined && 0 === checked.length) {
            alert(atLeastOnChecked);

            return true;
        }
    }

    var action = $(filterForm).attr('action');
    var data = $(filterForm).serialize();

    $(catalog).find('.catalog__item').fadeTo('fast', 0.3);
    $(filterForm).find('input, select').attr('disabled','disabled');
    $(filterForm).find('a').css('pointer-events', 'none');

    history.pushState({}, '', action + '?' + data);

    // Update sheets list
    $.get(action, data, function(response) {
        $(catalog).html(response);
        PubSub.publish('dom.added', catalog.parentNode);
    });

    return false;
}

export default CatalogFilters;
