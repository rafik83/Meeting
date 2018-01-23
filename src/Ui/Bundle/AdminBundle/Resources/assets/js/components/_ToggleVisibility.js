function ToggleVisibility(element, document)
{
    this.element = element;
    this.elementToHideId = this.element.getAttribute('data-element-id-to-hide');
    this.elementToHide = document.getElementById(this.elementToHideId);
    this.displayType = this.element.getAttribute('data-toggle-visibility-display-type');

    if (this.displayType === 'undefined' || this.displayType === 'undefined') {
        this.displayType = 'block';
    }

    this.element.addEventListener('click', this.onClick.bind(this));
}

ToggleVisibility.prototype.onClick = function (event)
{
    if (this.elementToHide.style.display === 'none') {
        this.elementToHide.style.display = this.displayType;
    } else {
        this.elementToHide.style.display = 'none';
    }
};

module.exports = ToggleVisibility;
