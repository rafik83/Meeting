function ShowPaymentInfo(element) {
    this.element = element;
    this.target = document.getElementById('payment-info-block');

    element.addEventListener('change', this.onChange.bind(this));
}

ShowPaymentInfo.prototype.onChange = function (event) {
    var paymentInfo = event.currentTarget.getAttribute('data-payment-info');

    if (true == paymentInfo) {
        this.target.classList.remove('hide');
        return;
    }

    this.target.classList.add('hide');
};

export default ShowPaymentInfo;
