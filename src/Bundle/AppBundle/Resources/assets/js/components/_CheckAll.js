function CheckAll(element, selector)
{
    this.element = element;
    this.all     = document.querySelectorAll(selector);

    this.element.addEventListener('change', this.check.bind(this));
}

CheckAll.prototype.check = function ()
{
    var checked = this.element.checked;

    [].forEach.call(this.all, function (element) {
        if (element.checked !== checked) {
            element.dispatchEvent(new Event('change'));
        }

        element.checked = checked;
    });
};

module.exports = CheckAll;
