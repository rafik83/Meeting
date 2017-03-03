'use strict';

/**
 * @constructor
 * @param {Object} batchForm
 */
function BatchSelectionHelper(batchForm) {
    this.batchForm = batchForm;
    this.helper = document.getElementById('batch-selection-helper');
    this.inputSelectionType = batchForm.querySelector('input[name="sheet_batch[selectionType]"]');

    this.helper.querySelector('.batch-helper-select-all')
        .addEventListener('click', this.toggleSelectionState.bind(this));
}

/**
 * @param {Object} event
 */
BatchSelectionHelper.prototype.toggleSelectionState = function (event) {
    console.log(this.inputSelectionType.value);

    if (this.inputSelectionType.value === 'selection_type_page') {
        this.selectAll(event.target);
    } else if(this.inputSelectionType.value === 'selection_type_all') {
        this.selectPage(event.target);
    }
};

BatchSelectionHelper.prototype.toggle = function () {
    this.helper.classList.toggle('hide');
};

/**
 * @param {Object} helper
 */
BatchSelectionHelper.prototype.selectAll = function (helper) {
    this.changeSelectionType('selection_type_all');
    var message = this.helper.querySelector('.batch-helper-message');

    message.innerHTML = message.dataset.allSelectedLabel;
    helper.innerHTML  = helper.dataset.cancelLabel;
};

/**
 * @param {Object} helper
 */
BatchSelectionHelper.prototype.selectPage = function (helper) {
    this.changeSelectionType('selection_type_page');
    var message = this.helper.querySelector('.batch-helper-message');

    message.innerHTML = message.dataset.pageSelectedLabel;
    helper.innerHTML  = helper.dataset.allLabel;
};

BatchSelectionHelper.prototype.changeSelectionType = function (type) {
    this.inputSelectionType.value = type;
};

module.exports = BatchSelectionHelper;
