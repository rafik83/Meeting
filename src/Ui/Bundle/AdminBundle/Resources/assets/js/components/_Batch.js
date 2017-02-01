
var CheckAllCheckbox    = require('./_CheckAllCheckbox');

/**
 * Batch component
 *
 * @param element
 * @constructor
 */
function Batch(element)
{
    this.element = element;
    this.batch   = false;

    // Add check all
    [].forEach.call(element.querySelectorAll('[data-batch-all]'), function (item) {
        new CheckAllCheckbox(item, item.getAttribute('data-batch-all'));
        item.addEventListener('change', this.toggle.bind(this));
    }.bind(this));

    // Toggle batch on each checkbox change
    [].forEach.call(element.querySelectorAll('tbody input[type=checkbox]'), function (item) {
        item.addEventListener('change', function (event) {
            this.toggle();
            this.closest(event.currentTarget, 'TR').classList.toggle('active');
        }.bind(this));
    }.bind(this));

    // Check row
    [].forEach.call(element.querySelectorAll('tbody tr'), function (item) {

        // Stop propagation on a, button, input or stopPropagation class
        [].forEach.call(item.querySelectorAll('a, button, input, .stopPropagation'), function (a) {
            a.addEventListener('click', function (event) { event.stopPropagation(); });
        });

        item.addEventListener('click', function (event) {
            event.preventDefault();
            var checkbox = item.querySelector('input[type=checkbox]');
            checkbox.checked = !checkbox.checked;

            var htmlEvent = document.createEvent('HTMLEvents');
            htmlEvent.initEvent('change', true, true);

            checkbox.dispatchEvent(htmlEvent);
        });
    });
}

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
 * Transchoice
 *
 * @param item
 * @param message
 * @param count
 */
Batch.prototype.transchoice = function (item, message, count)
{
    item.innerHTML = message.split('|')[count > 1 ? 1 : 0].replace('%count%', count);
};

module.exports = Batch;
