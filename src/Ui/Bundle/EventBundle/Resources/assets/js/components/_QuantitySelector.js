function QuantitySelector(element)
{
    this.input      = element.querySelector('input.qty');
    var max         = this.input.getAttribute('data-max');
    var min         = this.input.getAttribute('data-min');
    this.unitPrice  = this.input.getAttribute('data-unit-price');
    this.totalPrice = element.querySelector('.product-total-price');

    element.querySelector('.qtyminus').addEventListener('click', function () {
        if (null === min || min < this.input.value) {
            this.input.value--;
            this.updateTotalPrice();
        }
    }.bind(this));

    element.querySelector('.qtyplus').addEventListener('click', function () {
        if (null === max || max > this.input.value) {
            this.input.value++;
            this.updateTotalPrice();
        }
    }.bind(this));
}

QuantitySelector.prototype.updateTotalPrice = function ()
{
    this.totalPrice.innerHTML = this.unitPrice * this.input.value;
};

module.exports = QuantitySelector;
