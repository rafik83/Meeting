var $ = require('jquery');

require('select2');

function AjaxAutocomplete(element) {
    this.element = element;
    this.parentInput = document.getElementById(element.dataset.parentInput);

    this.initSelect(function (select2) {
        this.autocompleteElement = select2;

        this.prefill();

        // Select2 Event Listener
        this.autocompleteElement.on('select2:select', this.selectTag.bind(this));
        this.autocompleteElement.on('select2:unselect', this.unselectTag.bind(this));
    }.bind(this));
}

AjaxAutocomplete.prototype.initSelect = function (callback) {
    var select2 = $(this.element).select2({
        tags: true,
        multiple: true,
        data: [],
        delay: 250,
        minimumInputLength: 2,
        maximumInputLength: 50,
        placeholder: this.element.dataset.placeholder,
        tokenSeparators: [','],
        language: {
            noResults: '',
            errorLoading: '',
            inputTooShort: function () {
                return '';
            },
            searching: function () {
                return 'Recherche ...';
            }
        },
        ajax: {
            url: this.element.dataset.action,
            type: this.element.dataset.method,
            delay: 250,
            data: function (query) {
                return {
                    "query": query.term
                }
            },
            processResults: this.onSuccess
        }
    });

    callback.call(this, select2);
};

AjaxAutocomplete.prototype.selectTag = function () {
    this.updateParentInput();
};

AjaxAutocomplete.prototype.unselectTag = function () {
    this.updateParentInput();
};

AjaxAutocomplete.prototype.updateParentInput = function () {
    var localizations = $.map(this.autocompleteElement.select2('data'),
        function (localization) {
            if (localization.text != "") {
                return localization.text;
            }
        }
    ).join(',');

    this.parentInput.value = localizations.toString();
    this.parentInput.dispatchEvent(new Event('change'));
};

AjaxAutocomplete.prototype.prefill = function () {
    if (this.parentInput.value == '') {
        return;
    }

    var requestLocalizations = this.parentInput.value.split(',');

    requestLocalizations.forEach(function (localization) {
        var option = '<option selected="selected" value="' + localization + '">' + localization + '</option>';

        this.autocompleteElement.append(option)
    }.bind(this));

    this.autocompleteElement.trigger('change');
};

AjaxAutocomplete.prototype.onSuccess = function (data) {
    var results = [];

    $.map(data, function (localization) {
        results.push({
            id: localization.id,
            text: localization.name
        });
    });

    return {results: results};
};

module.exports = AjaxAutocomplete;
