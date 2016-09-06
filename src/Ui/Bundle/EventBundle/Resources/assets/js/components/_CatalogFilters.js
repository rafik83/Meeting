var $ = require('jquery');

function CatalogFilters(filterForm, catalogList)
{
    $(filterForm).find('input').on('change', function () {
        $(filterForm).submit();
    });

    $(filterForm).on('submit', function () {
        $(catalogList).find('.catalog__item').fadeTo('fast', 0.3);

        var action = $(filterForm).attr('action');
        var data = $(filterForm).serialize();

        // Update url
        history.pushState({}, '', action + '?' + data);

        // Update sheets list
        $.get(action, data, function(response) {
            $(catalogList).html(response);
        });

        return false;
    });
}

module.exports = CatalogFilters;
