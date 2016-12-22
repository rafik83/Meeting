var $ = require('jquery');

function CatalogPagination(element)
{
    this.element = element;
    this.element.addEventListener('click', function () {
        this.load()
    }.bind(this, this.element));
}

CatalogPagination.prototype.load = function ()
{
    $.ajax({
        url: "catalog/pagination",
        data: {page: (parseInt(this.element.getAttribute('data-page')) + 1)},
        dataType: 'html',
        success: function(html, status) {
            alert(html);
        }
    })
};

module.exports = CatalogPagination;
