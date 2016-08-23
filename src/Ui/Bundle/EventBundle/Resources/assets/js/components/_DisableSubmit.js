
function DisableSubmit(element)
{
    this.element = element;
    this.element.addEventListener('submit', this.onSubmit.bind(this));
}

DisableSubmit.prototype.onSubmit = function ()
{
    var submitSelector = '[form=' + this.element.id + ']';
    var submitButton   = document.querySelector(submitSelector);

    submitButton.disabled = true;
};

module.exports = DisableSubmit;
