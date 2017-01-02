var $ = require('jquery');

function SheetLoading() {
    this.load();
}

SheetLoading.prototype.load = function () {
    $.get(document.url, function (response) {
        $('.sheets-list').append(response.html);
    }).fail(function(error) {
        console.log(error);
    });
};

module.exports = SheetLoading;
