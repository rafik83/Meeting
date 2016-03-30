
function Form(element)
{
    this.element = element;
}

Form.prototype.get = function (name)
{
    var inputs = this.getByName(name);

    if (inputs.length === 0) {
        return null;
    }

    if (inputs.length === 1) {

        if (this.hasValue(inputs[0])) {
            return inputs[0].value;
        }

        if (this.hasChecked(inputs[0])) {
            return inputs[0].checked;
        }
    }
};

Form.prototype.set = function (name, value)
{
    var inputs = this.getByName(name);

    if (inputs.length === 0) {
        return;
    }

    if (inputs.length === 1) {

        if (this.hasValue(inputs[0])) {
            inputs[0].value = value;

            return;
        }

        if (this.hasChecked(inputs[0])) {
            inputs[0].checked = value;

            return;
        }
    }
};

Form.prototype.getByName = function (name)
{
    return this.element.querySelectorAll('[name="' + name + '"]');
};

Form.prototype.hasValue = function (input)
{
    return input.tagName === 'INPUT' && ['text', 'number', 'email'].indexOf(input.getAttribute('type')) !== -1 ||
        input.tagName === 'TEXTAREA' ||
        input.tagName === 'SELECT';
};

Form.prototype.hasChecked = function (input)
{
    return input.tagName === 'INPUT' && ['checkbox'].indexOf(input.getAttribute('type')) !== -1;
};

module.exports = Form;
