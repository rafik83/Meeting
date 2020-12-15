import $ from 'jquery';
import PubSub from 'pubsub-js';

function CatalogFilters(field, filterForm, catalog)
{
    if ('checkbox' === field.attr('type')) {
        const checked = $(filterForm).find('input[name="' + field.attr('name') + '"]:not(.disabled):checked');

        const atLeastOneChecked = $(field).closest('ul').data('message-at-least-one-checked');

        if (atLeastOneChecked !== undefined && 0 === checked.length) {
            alert(atLeastOneChecked);

            return true;
        }

        if (field.data('single-selection')) {
            const allCheckBoxes = field.closest('[data-choices-root]').find('[data-single-selection]');
            allCheckBoxes.each((index, sibling) => {
                if (sibling.value !== field.val()) {
                    sibling.checked = false;
                }
            });
        }
    }

    const action = $(filterForm).attr('action');
    const data = $(filterForm).serialize();

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
