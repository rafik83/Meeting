function AttributableProductToggleHappening(element, document)
{
    this.element = element;
    this.elementToHide = document.getElementById(this.element.getAttribute('data-element-id-to-hide'));
    this.happeningSelect = document.getElementById("product_update_option_happenings");

    this.init();
    this.element.addEventListener('click', this.onClick.bind(this));
}

AttributableProductToggleHappening.prototype.init = function ()
{
    if (false === this.element.checked) {
        this.elementToHide.style.display = 'none';
    }
};

AttributableProductToggleHappening.prototype.resetHappeningSelect = function ()
{
    if (false === this.element.checked) {
        this.happeningSelect.value = [];
        this.happeningSelect.dispatchEvent(new Event('change'));
    }
};

AttributableProductToggleHappening.prototype.onClick = function (event)
{
    if (this.elementToHide.style.display === 'none') {
        this.elementToHide.style.display = 'block';
    } else {
        this.elementToHide.style.display = 'none';
    }

    this.resetHappeningSelect();
};

export default AttributableProductToggleHappening;
