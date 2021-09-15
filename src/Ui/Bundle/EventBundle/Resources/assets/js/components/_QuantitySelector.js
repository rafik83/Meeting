function QuantitySelector(element)
{
    this.element = element;

    this.input = element.querySelector('input.qty');
    this.selectParticipants = element.querySelector('[data-select-participants]');
    this.quantityText = element.querySelector('[data-quantity-text]');

    this.unitPrice = parseFloat(element.getAttribute('data-unit-price'));
    this.included = parseInt(element.getAttribute('data-included'));
    this.selectedLineClass = element.getAttribute('data-selected-line-class');
    this.totalPrice = element.querySelector('.product-total-price');
    this.messageArea = element.querySelector('.message-error');

    var inputElement = this.input ? this.input : this.selectParticipants;

    this.max = parseInt(inputElement.getAttribute('data-max'));
    this.maxMessage = inputElement.getAttribute('data-max-message');
    this.min = parseInt(inputElement.getAttribute('data-min'));
    this.minMessage = inputElement.getAttribute('data-min-message');
    this.min = null !== this.min ? this.min : 0;

    if (this.input) {
        this.addQuantityEventListener();
    }

    if (this.selectParticipants) {
        $(this.selectParticipants).select2({
            width: '100%',
            placeholder: this.selectParticipants.getAttribute('data-placeholder'),
            maximumSelectionLength: this.max ? this.max : 0
        });

        $(this.selectParticipants).on('select2:close', this.updateTotalPriceFromSelectParticipants.bind(this));
    }
}

QuantitySelector.prototype.updateTotalPriceFromSelectParticipants = function ()
{
    var participantsNumber = $(this.selectParticipants).val() ? $(this.selectParticipants).val().length : 0;
    this.updateTotalPrice(Math.max(0, participantsNumber - this.included));
};

QuantitySelector.prototype.addQuantityEventListener = function ()
{
    this.element.querySelector('.qtyminus').addEventListener('click', function () {
        this.input.value--;
        this.updateTotalPriceFromQuantityInput();
    }.bind(this));

    this.element.querySelector('.qtyplus').addEventListener('click', function () {
        this.input.value++;
        this.updateTotalPriceFromQuantityInput();
    }.bind(this));

    this.input.addEventListener('change', function () {
        this.updateTotalPriceFromQuantityInput();
    }.bind(this));
};

QuantitySelector.prototype.updateTotalPriceFromQuantityInput = function ()
{
    this.updateTotalPrice(parseInt(this.input.value));
};

QuantitySelector.prototype.updateTotalPrice = function (value)
{
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

    if (this.input) {
        this.input.value = newValue;
    }

    if (this.quantityText) {
        this.quantityText.innerHTML = newValue;
    }

    this.totalPrice.innerHTML = this.unitPrice * newValue;
};

export default QuantitySelector;
