function QuantitySelector(element)
{
    this.element           = element;
    this.input             = element.querySelector('input.qty');
    this.max               = this.input.getAttribute('data-max');
    this.min               = this.input.getAttribute('data-min');
    this.included          = this.input.getAttribute('data-included');
    this.selectedLineClass = this.input.getAttribute('data-selected-line-class');
    this.min               = null !== this.min ? this.min : 0;
    this.unitPrice         = this.input.getAttribute('data-unit-price');
    this.totalPrice        = element.querySelector('.product-total-price');

    element.querySelector('.qtyminus').addEventListener('click', function () {
        this.input.value--;
        this.updateTotalPrice();
    }.bind(this));

    element.querySelector('.qtyplus').addEventListener('click', function () {
        this.input.value++;
        this.updateTotalPrice();
    }.bind(this));

    this.input.addEventListener('change', function () {
        this.updateTotalPrice();
    }.bind(this));
}

QuantitySelector.prototype.updateTotalPrice = function ()
{
    if (this.min >= this.input.value) {
        this.input.value = this.min;
    }

    if (null === this.max || this.max <= this.input.value) {
        this.input.value = this.max;
    }

    if (null !== this.selectedLineClass && 0 == this.included) {
        if (0 == this.input.value) {
            this.element.classList.remove('selected-line');
        } else {
            this.element.classList.add('selected-line');
        }
    }

    this.totalPrice.innerHTML = this.unitPrice * this.input.value;
};

module.exports = QuantitySelector;
