import CheckAllCheckbox from "./_CheckAllCheckbox";
import BatchSelectionHelper from "./_BatchSelectionHelper";

/**
 * Batch component
 *
 * @param {Object} element
 * @constructor
 */
function Batch(element)
{
    this.element = element;
    this.batch   = false;
    this.batchSelectionHelper = null;

    if (document.getElementById('batch-selection-helper') !== null) {
        this.batchSelectionHelper = new BatchSelectionHelper(element);
    }

    // Add check all
    [].forEach.call(element.querySelectorAll('[data-batch-all]'), function (item) {
        new CheckAllCheckbox(item, item.getAttribute('data-batch-all'));

        item.addEventListener('change', this.toggle.bind(this));
        item.addEventListener('click', this.toggleHelper.bind(this, true));
    }.bind(this));

    // Toggle batch on each checkbox change
    [].forEach.call(element.querySelectorAll('tbody input[type=checkbox]'), function (item) {
        item.addEventListener('change', function (event) {
            this.toggle();
            this.closest(event.currentTarget, 'TR').classList.toggle('active');

            var updateStateEvent = document.createEvent('HTMLEvents');
            updateStateEvent.initEvent('updateState-checkAllCheckbox', true, true);
            this.element.querySelector('[data-batch-all]').dispatchEvent(updateStateEvent);
        }.bind(this));

        item.addEventListener('click', this.toggleHelper.bind(this));
    }.bind(this));

    // Check row
    [].forEach.call(element.querySelectorAll('tbody tr'), function (item) {

        // Stop propagation on a, button, input or stopPropagation class
        [].forEach.call(item.querySelectorAll('.stopPropagation'), function (a) {
            a.addEventListener('click', function (event) { event.stopPropagation(); });
        });

        item.addEventListener('click', function (event) {
            if (event.target.tagName === 'A' ||
                event.target.tagName === 'BUTTON' ||
                event.target.tagName === 'INPUT') {
                return;
            }
            event.preventDefault();

            var checkbox = item.querySelector('input[type=checkbox]');
            checkbox.checked = !checkbox.checked;

            // Trigger and dispatch change event
            var htmlEvent = document.createEvent('HTMLEvents');
            htmlEvent.initEvent('change', true, true);
            checkbox.dispatchEvent(htmlEvent);

            this.toggleHelper(false);
        }.bind(this));
    }.bind(this));

    // Handle Batch Selection helper change
    this.element.addEventListener('change-batchSelectionHelper', function(event) {
        this.updateTranschoice(event.data[0].counts);
    }.bind(this));
}

/**
 * @param {Object} item
 * @param {string} tag
 *
 * @return {bool}
 */
Batch.prototype.closest = function (item, tag)
{
    if (item === null || item === undefined) {
        return null;
    }

    return item.tagName === tag ? item : this.closest(item.parentNode, tag);
};

/**
 * Count checked item
 *
 * @returns int
 */
Batch.prototype.count = function ()
{
    return this.element.querySelectorAll('tbody input[type=checkbox]:checked').length;
};

/**
 * Toggle batch mode
 */
Batch.prototype.toggle = function ()
{
    var count = this.count();

    // Toggle
    if (!this.batch && count > 0 || this.batch && count === 0) {

        // Show / hide stuff
        [].forEach.call(this.element.querySelectorAll('.batch-actions'), function (item) {
            item.classList.toggle('hide');
        });

        [].forEach.call(this.element.querySelectorAll('.new-actions'), function (item) {
            item.classList.toggle('hide');
        });

        this.batch = !this.batch;
    }

    // Transchoice on batch count
    [].forEach.call(this.element.querySelectorAll('[data-batch-transchoice]'), function (item) {
        this.transchoice(item, item.getAttribute('data-batch-transchoice'), count);
    }.bind(this));
};

/**
 * Toggle BatchSelectionHelper component
 *
 * @param {boolean} forceToggle
 * @see BatchSelectionHelper
 */
Batch.prototype.toggleHelper = function (forceToggle) {
    var force = forceToggle || false;
    var itemsPerPage = parseInt(this.element.dataset.pageItems);

    if (this.batchSelectionHelper !== null) {
        if (force === true) {
            this.batchSelectionHelper.toggle(); // show or hide batch selection helper;
        } else if (this.count() !== itemsPerPage) {
            this.batchSelectionHelper.disable();
        } else if (this.count() === itemsPerPage) {
            this.batchSelectionHelper.enable();
        }
    }
};

/**
 * @param {int} count
 */
Batch.prototype.updateTranschoice = function (count) {
    [].forEach.call(this.element.querySelectorAll('[data-batch-transchoice]'), function (item) {
        this.transchoice(item, item.getAttribute('data-batch-transchoice'), count);
    }.bind(this));
};

/**
 * Transchoice
 *
 * @param {Object} item
 * @param {string} message
 * @param {int} count
 */
Batch.prototype.transchoice = function (item, message, count)
{
    item.innerHTML = message.split('|')[count > 1 ? 1 : 0].replace('%count%', count);
};

export default Batch;
