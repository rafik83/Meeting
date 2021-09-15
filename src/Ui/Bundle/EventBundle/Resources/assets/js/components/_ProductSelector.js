/**
 * ProductSelector component
 *
 * @param element
 * @constructor
 */
function ProductSelector (element) {
    this.element = element;

    [].forEach.call(element.querySelectorAll('tbody input[type=radio]'), function (item) {
        if (item.checked) {
            this.closest(item, 'TR').classList.add('selected-line');
        }

        item.addEventListener('change', function (event) {
            [].forEach.call(element.querySelectorAll('tbody input[type=radio]'), function (item) {
                this.closest(item, 'TR').classList.remove('selected-line');
            }.bind(this));

            if (event.currentTarget.checked === true) {
                this.closest(event.currentTarget, 'TR').classList.add('selected-line');
            }
        }.bind(this));
    }.bind(this));

    [].forEach.call(element.querySelectorAll('tbody tr'), function (item) {
        item.addEventListener('click', function (event) {
            if (event.target.parentNode.tagName === 'BUTTON'
                || event.target.tagName === 'BUTTON'
                || event.target.tagName === 'INPUT'
            ) {
                return;
            }

            event.preventDefault();
            var checkbox     = item.querySelector('input[type=radio]');
            checkbox.checked = true;

            var htmlEvent = document.createEvent('HTMLEvents');
            htmlEvent.initEvent('change', true, true);

            checkbox.dispatchEvent(htmlEvent);
        });
    });
}

ProductSelector.prototype.closest = function (item, tag) {
    if (item === null || item === undefined) {
        return null;
    }

    return item.tagName === tag ? item : this.closest(item.parentNode, tag);
};

export default ProductSelector;
