'use strict';

/**
 * @constructor
 * @param {Object} batchForm
 */
function BatchSelectionHelper(batchForm) {
    this.batchForm = batchForm;
    this.helper = document.getElementById('batch-selection-helper');
    this.inputSelectionType = batchForm.querySelector('input[name="'+this.batchForm.name+'[selectionType]"]');

    this.helper.querySelector('.batch-helper-select-all')
        .addEventListener('click', this.toggleSelectionState.bind(this));
}

/**
 * @param {Object} event
 */
BatchSelectionHelper.prototype.toggleSelectionState = function (event) {
    if (this.inputSelectionType.value === 'selection_type_page') {
        this.selectAll(event.currentTarget);
    } else if(this.inputSelectionType.value === 'selection_type_all') {
        this.selectPage(event.currentTarget);
    }
};

BatchSelectionHelper.prototype.toggle = function () {
    this.helper.classList.toggle('hide');
};

BatchSelectionHelper.prototype.enable = function () {
    this.helper.classList.remove('hide');
};

BatchSelectionHelper.prototype.disable = function () {
    this.helper.classList.add('hide');
    this.selectPage(this.helper.querySelector('.batch-helper-select-all'));
};

/**
 * @param {Object} helper
 */
BatchSelectionHelper.prototype.selectAll = function (helper) {
    this.changeSelectionType('selection_type_all');
    var message = this.helper.querySelector('.batch-helper-message');

    message.innerHTML = message.dataset.allSelectedLabel;

    if (parseInt(helper.dataset.isSelectAllEnabled) === 1) {
        helper.innerHTML = helper.dataset.cancelLabel;
    }

    this.triggerChange(this.helper.dataset.allItems);
};

/**
 * @param {Object} helper
 */
BatchSelectionHelper.prototype.selectPage = function (helper) {
    this.changeSelectionType('selection_type_page');
    var message = this.helper.querySelector('.batch-helper-message');

    message.innerHTML = message.dataset.pageSelectedLabel;

    if (parseInt(helper.dataset.isSelectAllEnabled) === 1) {
        helper.innerHTML = helper.dataset.allLabel;
    }
};

BatchSelectionHelper.prototype.changeSelectionType = function (type) {
    this.inputSelectionType.value = type;
};

BatchSelectionHelper.prototype.triggerChange = function (totalSelection) {
    var htmlEvent = document.createEvent('HTMLEvents');
    htmlEvent.data = [{'counts': totalSelection}];

    htmlEvent.initEvent('change-batchSelectionHelper', true, true);

    this.batchForm.dispatchEvent(htmlEvent);
};

export default BatchSelectionHelper;
