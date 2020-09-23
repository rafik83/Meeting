import $ from 'jquery';
import PubSub from 'pubsub-js';
import 'select2';

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

        // Fix dropdown body positioned incorrectly when dropdownParent isn't statically positioned
        this.autocompleteElement.on('select2:open', function (e) {
            var y = $(window).scrollTop();
            $(window).scrollTop(y + 1);
        });
    }.bind(this));

    // Custom event
    var purgeEvent = this.element.getAttribute('data-purge-event');

    if (purgeEvent !== null) {
        PubSub.subscribe(purgeEvent, this.handlePurge.bind(this));
    }
}

AjaxAutocomplete.prototype.initSelect = function (callback) {
    var select2 = $(this.element).select2({
        tags: true,
        multiple: true,
        data: [],
        delay: 250,
        minimumInputLength: this.element.getAttribute('data-minimum-input-length'),
        placeholder: this.parentInput.getAttribute('data-placeholder'),
        tokenSeparators: ['|'],
        width: 'resolve',
        closeOnSelect: true,
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

AjaxAutocomplete.prototype.unselectTag = function (evt) {
    // prevent open select2 dropdown when removing tag
    if (!evt.params.originalEvent) {
        return;
    }
    evt.params.originalEvent.stopPropagation();

    this.updateParentInput();

    var isMobile = this.element.getAttribute('data-is-mobile');

    if (isMobile !== null) {
        this.removeDropdown();
    }
};

AjaxAutocomplete.prototype.onChange = function () {
    var onChangeCustomEvent = this.element.getAttribute('data-on-change-event');

    if (onChangeCustomEvent !== null) {
        this.removeDropdown();
        PubSub.publish(onChangeCustomEvent);
    }
};

/**
 * Remove UI select dropdown
 */
AjaxAutocomplete.prototype.removeDropdown = function () {
    document.querySelectorAll('.select2-dropdown').forEach(function (element) {
        $(element).remove();
    });
};

AjaxAutocomplete.prototype.handlePurge = function () {
    $(this.element).val(null);
    this.updateParentInput();
};

AjaxAutocomplete.prototype.updateParentInput = function () {
    var tags = $.map(this.autocompleteElement.select2('data'),
        function (tag) {
            if (tag.text != "") {
                return tag.text;
            }
        }
    ).join('|');

    this.parentInput.value = tags.toString();
    var htmlEvent = document.createEvent('HTMLEvents');
    htmlEvent.initEvent('change', true, true);

    this.parentInput.dispatchEvent(htmlEvent);
};

AjaxAutocomplete.prototype.prefill = function () {
    if (this.parentInput.value == '') {
        return;
    }

    var requestFilters = this.parentInput.value.split('|');

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

    return { results: results };
};

export default AjaxAutocomplete;
