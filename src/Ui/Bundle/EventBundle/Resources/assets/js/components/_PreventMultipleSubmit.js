
function PreventMultipleSubmit(element)
{
    this.element = element;
    this.element.addEventListener('submit', this.onSubmit.bind(this));
}

PreventMultipleSubmit.prototype.onSubmit = function ()
{
    var submitButton = this.element.querySelector('[type=submit]');

    if (submitButton.getAttribute('data-prevent-multiple-submit') == false) {
        return;
    }

    submitButton.disabled = true;
};

module.exports = PreventMultipleSubmit;
