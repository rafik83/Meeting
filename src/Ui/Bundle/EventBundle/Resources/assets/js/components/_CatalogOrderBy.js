var $ = require('jquery');

function CatalogOrderBy(orderByForm, catalogList)
{
    $(orderByForm).find('input').on('change', function () {
        $(orderByForm).submit();
    });

    $(orderByForm).on('submit', function () {
        $(catalogList).find('.catalog__item').fadeTo('fast', 0.3);

        var action = $(orderByForm).attr('action');
        var data = $(orderByForm).serialize();

        // Update url
        history.pushState({}, '', action + '?' + data);

        // Update sheets list
        $.get(action, data, function(response) {
            $(catalogList).html(response);
        });

        return false;
    });
}

module.exports = CatalogOrderBy;
