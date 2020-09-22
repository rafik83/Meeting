function CheckAllButton(element, selector, check)
{
    this.element  = element;
    this.selector = selector;
    this.check    = check;

    this.element.addEventListener('click', this.checkAll.bind(this));
}

CheckAllButton.prototype.checkAll = function ()
{
    [].forEach.call(document.querySelectorAll(this.selector), function (element) {
        element.checked = this.check;
    }.bind(this));
};

export default CheckAllButton;
