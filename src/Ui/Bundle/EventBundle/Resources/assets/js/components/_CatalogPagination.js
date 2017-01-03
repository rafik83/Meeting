var $ = require('jquery');
var PubSub = require('pubsub-js');

function CatalogPagination(element)
{
    this.element = element;
    this.element.addEventListener('click', function () {
        this.load()
    }.bind(this, this.element));
}

CatalogPagination.prototype.load = function ()
{
    this.element.setAttribute('disabled', 'disabled');

    var newDataPage = (parseInt(this.element.getAttribute('data-page')) + 1);
    $.ajax({
        url: document.url,
        data: {page: newDataPage},
        dataType: "json",
        success: function (json) {

            var seeMoreButton = $('.see-more');

            seeMoreButton.removeAttr('disabled');
            if (!json.seeMoreButton) {
                seeMoreButton.hide();
            }

            $('.catalog__list').append(json.html);
            seeMoreButton.attr("data-page", newDataPage);

            PubSub.publish('dom.added', document.getElementById('catalog-page-'+newDataPage));
        }
    })
};

module.exports = CatalogPagination;
