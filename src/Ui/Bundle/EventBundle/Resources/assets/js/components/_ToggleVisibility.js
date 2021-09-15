function ToggleVisibility(element)
{
    this.element = element;
    this.hideButton = this.element.querySelector('[data-toggle-visibility-hide]');
    this.showButton = this.element.querySelector('[data-toggle-visibility-show]');
    this.elementToOpen = this.element.querySelector('[data-toggle-visibility-element-to-open]');
    this.displayType = this.element.getAttribute('data-toggle-visibility-display-type');

    if (this.displayType === 'undefined' || this.displayType === null) {
        this.displayType = 'block';
    }

    this.hide(this.elementToOpen);
    this.showButton.addEventListener('click', this.handleShow.bind(this));
    this.hideButton.addEventListener('click', this.handleHide.bind(this));
}

ToggleVisibility.prototype.handleShow = function (event)
{
    event.preventDefault();

    this.show(this.elementToOpen);
    this.hide(this.showButton);
    this.show(this.hideButton);
};

ToggleVisibility.prototype.handleHide = function (event)
{
    event.preventDefault();

    this.hide(this.elementToOpen);
    this.hide(this.hideButton);
    this.show(this.showButton);
};

ToggleVisibility.prototype.show = function (element)
{
    if (!element) return;

    element.style.display = this.displayType;
};

ToggleVisibility.prototype.hide = function (element)
{
    if (!element) return;

    element.style.display = 'none';
};

export default ToggleVisibility;
