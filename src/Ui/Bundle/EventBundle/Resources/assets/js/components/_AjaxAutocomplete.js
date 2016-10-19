require('select2');

var $ = require('jquery');

function AjaxAutocomplete(element) {
    this.element = element;

    $(element).select2();

    this.element.addEventListener('keypress', this.onChange.bind(this));
}

AjaxAutocomplete.prototype.onChange = function () {
    if (this.element.value.length > 2) {
        $.ajax({
            url: this.element.dataset.action,
            method: this.element.dataset.method,
            data: { 'query': this.element.value }
        }).done(this.onSuccess.bind(this));
    }
};

AjaxAutocomplete.prototype.onSuccess = function()
{

};

module.exports = AjaxAutocomplete;
