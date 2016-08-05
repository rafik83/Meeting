function ChoiceDescription(element) {
    this.element = element;
    this.target = document.querySelector(element.getAttribute('data-choice-description'));

    [].forEach.call(this.element.querySelectorAll('[data-description]'), function (input) {
        input.addEventListener('change', this.onChange.bind(this));
    }.bind(this));
}

function ChoicePaymentInfo(element) {
    this.element = element;
    this.target = document.getElementById('payment-info-block');

    element.addEventListener('change', this.onChange.bind(this));
}

ChoicePaymentInfo.prototype.onChange = function (event) {
    var paymentInfo = event.currentTarget.getAttribute('data-payment-info');

    console.log(paymentInfo);
    if (1 == paymentInfo) {
        this.target.classList.remove('hide');
        return;
    }

    this.target.classList.add('hide');
};

ChoiceDescription.prototype.onChange = function (event) {
    var description = event.currentTarget.getAttribute('data-description');

    if ('' != description) {
        this.target.innerHTML = description;
        this.target.classList.remove('hide');

        return;
    }

    this.target.classList.add('hide');
};

module.exports = ChoiceDescription;
module.exports = ChoicePaymentInfo;
