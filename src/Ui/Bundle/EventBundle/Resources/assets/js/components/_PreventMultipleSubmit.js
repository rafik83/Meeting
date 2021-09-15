function PreventMultipleSubmit(element)
{
    this.element = element;
    this.element.addEventListener('submit', this.onSubmit.bind(this));
}

PreventMultipleSubmit.prototype.onSubmit = function ()
{
    setTimeout(function () {
        [].forEach.call(this.element.querySelectorAll('[type=submit]'), function (element) {
            if (element.getAttribute('data-prevent-multiple-submit') !== false) {
                element.disabled = true;
            }
        });
    }.bind(this), 1);
};

export default PreventMultipleSubmit;
