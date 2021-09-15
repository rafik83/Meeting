function CheckAllCheckbox(element, selector)
{
    this.element = element;
    this.all     = document.querySelectorAll(selector);

    this.element.addEventListener('change', this.check.bind(this));
    this.element.addEventListener('updateState-checkAllCheckbox', this.updateState.bind(this));

    [].forEach.call(this.all, function (item) {
        item.addEventListener('change', function () {
            if (this.count() === 0) {
                this.element.checked = false;
            }
        }.bind(this));
    }.bind(this));
}

CheckAllCheckbox.prototype.check = function ()
{
    var checked = this.element.checked;

    [].forEach.call(this.all, function (element) {
        var old = element.checked;

        element.checked = checked;

        if (old !== checked) {
            var htmlEvent = document.createEvent('HTMLEvents');
            htmlEvent.initEvent('change', true, true);

            element.dispatchEvent(htmlEvent);

        }
    });
};

CheckAllCheckbox.prototype.count = function ()
{
    return [].reduce.call(this.all, function (previous, current) { return current.checked ? ++previous : previous }, 0);
};

/**
 * Update check all state
 */
CheckAllCheckbox.prototype.updateState = function () {
    this.element.checked = this.count() === this.all.length;
};

export default CheckAllCheckbox;
