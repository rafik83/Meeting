var $ = require('jquery');

function SheetLoading() {
    this.load();
}

SheetLoading.prototype.load = function () {
    $.ajax({
        url: document.url,
        dataType: "json",
        success: function (json) {
            $('.sheets-list').append(json.html);
        }
    })
};

module.exports = SheetLoading;
