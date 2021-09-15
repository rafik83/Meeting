import $ from 'jquery';

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
    var count            = this.count();
    var toCheck          = count === 0 ? 'checked' : '';
    var toDisable        = count === 0 ? 'disabled' : '';
    var noPreferenceHtml = '<div class="checkbox noPreferenceCheckbox"><label class="control-label"><input type="checkbox" ' + toDisable + ' ' + toCheck + '>' + this.placeholder + '</label></div>';

    var node = document.createElement("div");
    node.innerHTML = noPreferenceHtml;

    this.element.appendChild(node);

    [].forEach.call(this.element.getElementsByClassName("noPreferenceCheckbox"), function (element) {
        var checkbox = element.querySelector('input[type=checkbox]');
        checkbox.addEventListener('click', function () {
            if (checkbox.checked) {
                [].forEach.call(this.all, function (item) {
                    item.checked = false;
                });
                checkbox.disabled = true;
            }
        }.bind(this));
    }.bind(this));
};

CatalogSheetCardRequestCheckbox.prototype.checkNoPreference = function ()
{
    [].forEach.call(this.element.getElementsByClassName("noPreferenceCheckbox"), function (element) {
        element.querySelector('input[type=checkbox]').checked  = true;
        element.querySelector('input[type=checkbox]').disabled = true;
    }.bind(this));
};

CatalogSheetCardRequestCheckbox.prototype.uncheckNoPreference = function ()
{
    [].forEach.call(this.element.getElementsByClassName("noPreferenceCheckbox"), function (element) {
        element.querySelector('input[type=checkbox]').checked  = false;
        element.querySelector('input[type=checkbox]').disabled = false;
    }.bind(this));
};

CatalogSheetCardRequestCheckbox.prototype.count = function ()
{
    return [].reduce.call(this.all, function (previous, current) {
        return current.checked ? ++previous : previous
    }, 0);
};

export default CatalogSheetCardRequestCheckbox;
