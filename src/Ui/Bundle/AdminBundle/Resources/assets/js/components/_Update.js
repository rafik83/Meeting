/**
 * Inline edit
 *
 * <td data-update="{ id: 1, property: 'quantity': value: 5 }" data-url="/api/foobar/update">
 *     5
 * </td>
 */
function Update(element)
{
    this.element = element;
    this.data = JSON.parse(element.getAttribute('data-update'));
    this.url = element.getAttribute('data-url');
    this.type = element.getAttribute('data-type');
    this.inputSize = element.getAttribute('data-input-size');
    this.inputType = element.getAttribute('data-input-type');
    this.editing = false;
    this.old = null;

    if ('textarea' === this.inputType) {
        this.input = document.createElement('textarea');
    } else {
        this.input = document.createElement('input');
    }

    if (null === this.inputSize) {
        this.input.setAttribute('size', '6');
    } else {
        this.input.setAttribute('size', this.inputSize);
    }

    this.element.addEventListener('click', this.clicked.bind(this), false);
    this.input.addEventListener('blur', this.blured.bind(this));
    this.input.addEventListener('keypress', this.keyupped.bind(this));
    this.input.addEventListener('keyup', this.keyupped.bind(this));

    this.placeholder();
}

Update.prototype.clicked = function (event)
{
    if (this.editing) {
        return;
    }

    event.preventDefault();

    this.editing           = true;
    this.input.value       = this.data.value;
    this.element.innerHTML = null;
    this.element.appendChild(this.input);
    this.input.focus();
};

Update.prototype.blured = function (event)
{
    this.save();
};

Update.prototype.placeholder = function ()
{
    if (this.data.value === null || this.data.value === '') {
        this.element.innerHTML = '<span class="glyphicon glyphicon-edit"></span>';
    } else {
        this.element.textContent = this.data.value;
    }
};

Update.prototype.close = function ()
{
    this.editing = false;

    this.placeholder();
};

Update.prototype.getInputValue = function ()
{
    return this.parseValue(this.input.value);
};

Update.prototype.parseValue = function (value)
{
    if (this.type === 'int') {
        return parseInt(value, 10);
    }

    if (this.type === 'float') {
        return parseFloat(value);
    }

    return value;
};

Update.prototype.save = function ()
{
    if (this.editing === false) {
        return;
    }

    if (this.data.value != this.input.value) {

        this.old        = this.data.value;
        this.data.value = this.getInputValue();

        var xhr = new XMLHttpRequest();
        xhr.open('POST', this.url);
        xhr.onload = function (event) {
            var updatetableInfo = document.getElementById('updatetable-info');
            if (updatetableInfo !== null) {
                updatetableInfo.parentNode.removeChild(updatetableInfo);
            }
            if (event.target.status !== 200) {
                alert(JSON.parse(event.target.response).error);
                this.revert();
            } else {
                var response = JSON.parse(event.target.response);

                if (response.info !== undefined) {
                    var span = document.createElement('span');
                    span.setAttribute('id', 'updatetable-info');
                    span.setAttribute('class', 'label label-warning');
                    span.innerHTML = response.info;
                    this.element.parentNode.appendChild(span);
                }
            }
        }.bind(this);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        xhr.send(JSON.stringify(this.data));
    }

    this.close();
};

Update.prototype.revert = function ()
{
    this.data.value = this.old;
    this.close();
};

Update.prototype.keyupped = function (event)
{
    if (this.editing === false) {
        return;
    }

    var code = event.keyCode || event.which;

    if (code === 27) {
        this.close();
    } else if (code === 13 && !event.shiftKey) {
        event.preventDefault();
        this.save();
        return false;
    }
};

export default Update;
