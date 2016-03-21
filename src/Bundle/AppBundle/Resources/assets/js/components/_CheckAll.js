function CheckAll(element)
{
    this.element = element;
    this.all     = document.querySelectorAll(this.element.getAttribute('data-check-all'));

    this.element.addEventListener('change', this.check.bind(this));
}

CheckAll.prototype.check = function ()
{
    var checked = this.element.checked;

    [].forEach.call(this.all, function (element) {
        element.checked = checked;
    });
};

module.exports = CheckAll;
