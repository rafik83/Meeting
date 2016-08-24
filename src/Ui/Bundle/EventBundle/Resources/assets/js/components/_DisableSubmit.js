var $ = require('jquery');

function DisableSubmit(element)
{
    this.element = element;
    this.element.addEventListener('submit', this.onSubmit.bind(this));
}

DisableSubmit.prototype.onSubmit = function ()
{
    var submitButton = null;

    if (this.element.id !== '') {
        var submitSelector = '[form=' + this.element.id + ']';
        submitButton       = document.querySelector(submitSelector);
        submitButton.disabled = true;
    }

    if (null === submitButton) {
        submitButton = $(this.element).find('[type=submit]');
        submitButton.attr('disabled', 'disabled');
    }
};

module.exports = DisableSubmit;
