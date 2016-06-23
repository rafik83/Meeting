function QuantitySelector(element)
{
    this.element           = element;
    this.input             = element.querySelector('input.qty');
    this.messageArea       = element.querySelector('.message-error');
    this.max               = parseInt(this.input.getAttribute('data-max'));
    this.maxMessage        = this.input.getAttribute('data-max-message');
    this.min               = parseInt(this.input.getAttribute('data-min'));
    this.minMessage        = this.input.getAttribute('data-min-message');
    this.included          = parseInt(this.input.getAttribute('data-included'));
    this.selectedLineClass = this.input.getAttribute('data-selected-line-class');
    this.min               = null !== this.min ? this.min : 0;
    this.unitPrice         = parseFloat(this.input.getAttribute('data-unit-price'));
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
    var value = parseInt(this.input.value);

    if (isNaN(value)) {
        value = 0;
    }

    var newValue = value;

    if (!isNaN(this.min) && value < this.min) {
        newValue = this.min;

        if (null !== this.messageArea && this.minMessage !== null) {
            this.messageArea.innerHTML = this.minMessage;
        }
    } else if (!isNaN(this.max) && this.max < value) {
        newValue = this.max;

        if (null !== this.messageArea && this.maxMessage !== null) {
            this.messageArea.innerHTML = this.maxMessage;
        }
    } else {
        if (null !== this.messageArea) {
            this.messageArea.innerHTML = '';
        }
    }


    if (null !== this.selectedLineClass && 0 === this.included) {
        if (0 === newValue) {
            this.element.classList.remove('selected-line');
        } else {
            this.element.classList.add('selected-line');
        }
    }

    this.input.value = newValue;
    this.totalPrice.innerHTML = this.unitPrice * newValue;
};

module.exports = QuantitySelector;
