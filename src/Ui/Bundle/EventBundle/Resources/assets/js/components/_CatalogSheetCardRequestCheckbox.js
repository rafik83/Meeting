var $ = require('jquery');

function CatalogSheetCardRequestCheckbox(element)
{
    this.element     = element;
    this.all         = element.querySelectorAll('.request-checkbox-select-participant');
    this.placeholder = element.getAttribute('data-placeholder-preference');

    [].forEach.call(this.all, function (item) {
        item.addEventListener('change', function (event) {
            if (this.count() === 0) {
                this.checkNoPreference();
            } else {
                this.uncheckNoPreference();
            }
        }.bind(this));
    }.bind(this));


    this.displayNoPreference();
}

CatalogSheetCardRequestCheckbox.prototype.displayNoPreference = function ()
{
    var toCheck          = this.count() === 0 ? 'checked' : '';
    var noPreferenceHtml = '<div class="checkbox noPreferenceCheckbox"><label class="control-label"><input type="checkbox" disabled ' + toCheck + '>' + this.placeholder + '</label></div>';

    var node = document.createElement("div");
    node.innerHTML = noPreferenceHtml;

    this.element.appendChild(node);
};

CatalogSheetCardRequestCheckbox.prototype.checkNoPreference = function ()
{
    [].forEach.call(this.element.getElementsByClassName("noPreferenceCheckbox"), function (element) {
        element.querySelector('input[type=checkbox]').checked = true;
    }.bind(this));
};

CatalogSheetCardRequestCheckbox.prototype.uncheckNoPreference = function ()
{
    [].forEach.call(this.element.getElementsByClassName("noPreferenceCheckbox"), function (element) {
        element.querySelector('input[type=checkbox]').checked = false;
    }.bind(this));
};

CatalogSheetCardRequestCheckbox.prototype.count = function ()
{
    return [].reduce.call(this.all, function (previous, current) {
        return current.checked ? ++previous : previous
    }, 0);
};

module.exports = CatalogSheetCardRequestCheckbox;
