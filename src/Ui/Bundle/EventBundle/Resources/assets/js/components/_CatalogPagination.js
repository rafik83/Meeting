import $ from 'jquery';
import PubSub from 'pubsub-js';

function CatalogPagination(element)
{
    this.element = element;
    this.element.addEventListener('click', function () {
        this.loadNextPage()
    }.bind(this, this.element));
}

CatalogPagination.prototype.loadNextPage = function ()
{
    this.element.setAttribute('disabled', 'disabled');

    var page = (parseInt(this.element.getAttribute('data-page')) + 1);

    $.ajax({
        url: document.location.href,
        data: {page: page},
        dataType: "json",
        success: function (json) {
            var seeMoreButton = $('.see-more');

            seeMoreButton.removeAttr('disabled');

            if (!json.seeMoreButton) {
                seeMoreButton.hide();
            }

            var pageId = 'catalog-page-' + page;

            $('.catalog__list').append('<div id="' + pageId + '">' + json.html + '</div>');
            seeMoreButton.attr("data-page", page);

            PubSub.publish('dom.added', document.getElementById(pageId));
        }
    })
};

export default CatalogPagination;
