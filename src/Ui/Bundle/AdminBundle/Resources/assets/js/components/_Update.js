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
    this.data    = JSON.parse(element.getAttribute('data-update'));
    this.url     = element.getAttribute('data-url');
    this.type    = element.getAttribute('data-type');
    this.editing = false;
    this.old     = null;
    this.input   = document.createElement('input');
    this.input.setAttribute('size', '6');
    this.element.addEventListener('click', this.clicked.bind(this), false);
    this.input.addEventListener('blur', this.blured.bind(this));
    this.input.addEventListener('keypress', this.keyupped.bind(this));
    this.input.addEventListener('keyup', this.keyupped.bind(this));
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

Update.prototype.close = function ()
{
    this.editing           = false;
    this.element.innerHTML = this.data.value;
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
            if (event.target.status !== 200) {
                alert(JSON.parse(event.target.response).error);
                this.revert();
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
    } else if (code === 13) {
        event.preventDefault();
        this.save();
        return false;
    }
};

module.exports = Update;
