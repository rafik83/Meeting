var $ = require('jquery');
var PubSub = require('pubsub-js');

require('select2');

/**
 * @param {Node} element
 * @constructor
 */
function AjaxAutocomplete(element) {
    this.element = element;
    this.parentInput = document.getElementById(element.getAttribute('data-parent-input'));

    this.initSelect(function (select2) {
        this.autocompleteElement = select2;

        this.prefill();

        // Select2 Event Listener
        this.autocompleteElement.on('select2:select', this.selectTag.bind(this));
        this.autocompleteElement.on('select2:unselect', this.unselectTag.bind(this));
        this.autocompleteElement.on('change', this.onChange.bind(this));
    }.bind(this));
}

AjaxAutocomplete.prototype.initSelect = function (callback) {
    var select2 = $(this.element).select2({
        tags: true,
        multiple: true,
        data: [],
        delay: 250,
        minimumInputLength: this.element.getAttribute('data-minimum-input-length'),
        placeholder: this.parentInput.getAttribute('data-placeholder'),
        tokenSeparators: [','],
        width: 'resolve',
        language: {
            noResults: function () {
                return ''
            },
            errorLoading: function () {
                return ''
            },
            inputTooShort: function () {
                return this.element.getAttribute('data-label-input-too-short');
            }.bind(this),
            searching: function () {
                return this.element.getAttribute('data-label-searching');
            }.bind(this)
        },
        ajax: {
            url: this.element.getAttribute('data-action'),
            type: this.element.getAttribute('data-method'),
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

AjaxAutocomplete.prototype.onChange = function () {
    var onChangeCustomEvent = this.element.dataset.onChangeEvent;

    if (onChangeCustomEvent !== null) {
        PubSub.publish(onChangeCustomEvent);
    }
};

AjaxAutocomplete.prototype.updateParentInput = function () {
    var tags = $.map(this.autocompleteElement.select2('data'),
        function (tag) {
            if (tag.text != "") {
                return tag.text;
            }
        }
    ).join(',');

    this.parentInput.value = tags.toString();
    var htmlEvent = document.createEvent('HTMLEvents');
    htmlEvent.initEvent('change', true, true);

    this.parentInput.dispatchEvent(htmlEvent);
};

AjaxAutocomplete.prototype.prefill = function () {
    if (this.parentInput.value == '') {
        return;
    }

    var requestFilters = this.parentInput.value.split(',');

    requestFilters.forEach(function (filter) {
        var option = '<option selected="selected" value="' + filter + '">' + filter + '</option>';

        this.autocompleteElement.append(option)
    }.bind(this));

    this.autocompleteElement.trigger('change');
};

AjaxAutocomplete.prototype.onSuccess = function (data) {
    var results = [];

    $.map(data, function (result) {
        results.push({
            id: result.id,
            text: result.name
        });
    });

    return {results: results};
};

module.exports = AjaxAutocomplete;
