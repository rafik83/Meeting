
function Form(element)
{
    this.element = element;
}

Form.prototype.get = function (name)
{
    var inputs = this.getByName(name);

    if (inputs.length === 1) {

        if (this.isMultipleSelect(inputs[0])) {
            return [].map.call(
                [].filter.call(inputs[0].querySelectorAll('option'), function (option) { return option.selected; }),
                function (option) { return option.value }
            );
        }

        if (this.hasValue(inputs[0])) {
            return inputs[0].value;
        }

        if (this.hasChecked(inputs[0])) {
            return inputs[0].checked;
        }
    }

    if (inputs.length > 1) {

        if (this.areRadios(inputs)) {
            return this.getCheckedRadioValue(inputs);
        }

        if (this.areCheckboxes(inputs)) {
            return this.getCheckedCheckboxesValues(inputs);
        }

    }

    return null;
};

Form.prototype.set = function (name, value)
{
    if (value === undefined || null === value) {
        return;
    }

    var inputs = this.getByName(name);

    if (inputs.length === 1) {

        if (this.isMultipleSelect(inputs[0])) {

            [].forEach.call(inputs[0].querySelectorAll('option'), function (option) {
                option.selected = -1 !== value.indexOf(option.value);
            });

            inputs[0].dispatchEvent(new Event('change'));

            return;
        }

        if (this.hasValue(inputs[0])) {
            inputs[0].value = value;

            return;
        }

        if (this.hasChecked(inputs[0])) {
            inputs[0].checked = value;

            return;
        }
    }

    if (inputs.length > 1) {

        if (this.areRadios(inputs)) {
            this.checkRadio(inputs, value);

            return;
        }

        if (this.areCheckboxes(inputs)) {
            this.checkCheckboxes(inputs, value);
        }

    }
};

Form.prototype.getByName = function (name)
{
    return this.element.querySelectorAll('[name="' + name + '"]');
};

Form.prototype.hasValue = function (input)
{
    return this.isText(input) || this.isEmail(input) || this.isNumber(input) || this.isSelect(input) || this.isTextarea(input);
};

Form.prototype.hasChecked = function (input)
{
    return this.isCheckbox(input);
};

Form.prototype.isText = function (input)
{
    return this.isInput(input, 'text');
};

Form.prototype.isNumber = function (input)
{
    return this.isInput(input, 'number');
};

Form.prototype.isEmail = function (input)
{
    return this.isInput(input, 'email')
};

Form.prototype.isInput = function (input, type)
{
    return input.tagName === 'INPUT' && input.getAttribute('type') === type;
};

Form.prototype.isSelect = function (input)
{
    return input.tagName === 'SELECT';
};

Form.prototype.isTextarea = function (input)
{
    return input.tagName === 'TEXTAREA';
};

Form.prototype.isCheckbox = function (input)
{
    return this.isInput(input, 'checkbox');
};

Form.prototype.isRadio = function (input)
{
    return this.isInput(input, 'radio');
};

Form.prototype.areRadios = function (inputs)
{
    return [].reduce.call(inputs, function (previous, input) {
        return previous && this.isRadio(input);
    }.bind(this));
};

Form.prototype.areCheckboxes = function (inputs)
{
    return [].reduce.call(inputs, function (previous, input) {
        return previous && this.isCheckbox(input);
    }.bind(this));
};

Form.prototype.getCheckedCheckboxesValues = function (inputs)
{
    var checked = [].filter.call(inputs, function (input) {
        return input.checked;
    });

    [].reduce.call(checked , function (previous, input) {
        return previous.push(input.value);
    }, []);
};

Form.prototype.checkCheckboxes = function (inputs, values)
{
    for (var i = 0; i < inputs.length; i++) {
        inputs[0].checked = (values.indexOf(inputs[0].value) !== -1)
    }
};

Form.prototype.getCheckedRadioValue = function (inputs)
{
    var checked = [].filter.call(inputs, function (input) {
        return input.checked;
    });

    return checked.length > 0 ? checked[0].value : null;
};

Form.prototype.checkRadio = function (inputs, value)
{
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].checked = (value === inputs[i].value);
    }
};

Form.prototype.isMultipleSelect = function (input)
{
    return input.tagName === 'SELECT' && input.multiple === true;
};

Form.prototype.bind = function (name, value)
{
    var element = this.element.querySelector('[data-bind="' + name + '"]');

    if (element !== null && element !== undefined) {
        element.innerHTML = value ? value : '...';
    }
};

export default Form;
