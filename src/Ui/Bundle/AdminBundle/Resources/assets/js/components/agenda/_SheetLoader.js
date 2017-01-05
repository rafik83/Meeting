var $ = require('jquery');

function SheetLoader(element) {
    this.element = element;
    this.load();
}

SheetLoader.prototype.load = function () {
    $.get(document.location.href, function (response) {
        $('.sheets-list').append(response.html);
    }).fail(function(error) {
        console.log(error);
    });
};

module.exports = SheetLoader;
