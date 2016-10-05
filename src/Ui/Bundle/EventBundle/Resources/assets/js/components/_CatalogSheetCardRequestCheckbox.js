var $ = require('jquery');

function CatalogSheetCardRequestCheckbox(element)
{
    this.element     = element;
    this.all         = element.querySelectorAll('.request-checkbox-select-participant');
    this.placeholder = element.getAttribute('data-placeholder-preference');

    [].forEach.call(this.all, function (item) {
        item.addEventListener('change', function (event) {
            if (this.count() === 0) {
                this.displayNoPreference();
            } else {
                this.hideNoPreference();
            }
        }.bind(this));
    }.bind(this));

    if (this.count() === 0) {
        this.displayNoPreference();
    }
}

CatalogSheetCardRequestCheckbox.prototype.displayNoPreference = function ()
{
    var noPreferenceHtml = '<div class="checkbox noPreferenceCheckbox"><label class="control-label"><input type="checkbox" disabled checked>' + this.placeholder + '</label></div>';

    var node = document.createElement("div");
    node.innerHTML = noPreferenceHtml;

    this.element.appendChild(node);
};

CatalogSheetCardRequestCheckbox.prototype.hideNoPreference = function ()
{
    [].forEach.call(this.element.getElementsByClassName("noPreferenceCheckbox"), function (element) {
        $(element).remove();
    }.bind(this));
};

CatalogSheetCardRequestCheckbox.prototype.count = function ()
{
    return [].reduce.call(this.all, function (previous, current) {
        return current.checked ? ++previous : previous
    }, 0);
};

module.exports = CatalogSheetCardRequestCheckbox;
