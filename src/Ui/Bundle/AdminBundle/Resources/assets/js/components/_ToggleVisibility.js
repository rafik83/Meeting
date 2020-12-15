function ToggleVisibility(element, document)
{
    this.element = element;
    this.elementToHideId = this.element.getAttribute('data-element-id-to-hide');
    this.elementToHide = document.getElementById(this.elementToHideId);
    this.displayType = this.element.getAttribute('data-toggle-visibility-display-type');

    if (this.displayType === 'undefined' || this.displayType === null) {
        this.displayType = 'block';
    }

    if ('input' === this.element.tagName.toLowerCase()) {
        this.element.addEventListener('change', this.onClick.bind(this));
    } else {
        this.element.addEventListener('click', this.onClick.bind(this));
    }
}

ToggleVisibility.prototype.onClick = function (event)
{
    if (this.elementToHide.style.display === 'none') {
        this.elementToHide.style.display = this.displayType;
    } else {
        this.elementToHide.style.display = 'none';
    }
};

export default ToggleVisibility;
