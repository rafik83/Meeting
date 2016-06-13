function QuantitySelector(element)
{
    this.input      = element.querySelector('input.qty');
    this.max        = this.input.getAttribute('data-max');
    this.min        = this.input.getAttribute('data-min');
    this.min        = null !== this.min ? this.min : 0;
    this.unitPrice  = this.input.getAttribute('data-unit-price');
    this.totalPrice = element.querySelector('.product-total-price');

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

    this.totalPrice.innerHTML = this.unitPrice * this.input.value;
};

module.exports = QuantitySelector;
