/**
 * inline edit with reconciliation if the data is also elsewhere
 *
 * @param element
 */
function UpdateAndReconcile(element) {
    this.element = element;
    this.data = JSON.parse(this.element.getAttribute('data-update-and-reconcile'));
    this.url = element.getAttribute('data-url');
    this.type = element.getAttribute('data-type');
    this.editing = false;
    this.old = null;
    this.selectorToReconcile = element.getAttribute('data-to-reconcile');

    this.input = document.createElement('input');
    this.input.style.color = 'black';
    this.element.addEventListener('click', this.clicked.bind(this), false);
    this.input.addEventListener('blur', this.blured.bind(this));
    this.input.addEventListener('keypress', this.keyupped.bind(this));
    this.input.addEventListener('keyup', this.keyupped.bind(this));

    this.placeholder();
}

UpdateAndReconcile.prototype.clicked = function (event)
{
    if (this.editing) {
        return;
    }

    event.preventDefault();

    this.editing           = true;
    this.data              = JSON.parse(this.element.getAttribute('data-update-and-reconcile'));
    this.input.value       = this.data.value;
    this.element.innerHTML = null;
    this.element.appendChild(this.input);
    this.input.focus();
};

UpdateAndReconcile.prototype.blured = function (event)
{
    this.save();
};

UpdateAndReconcile.prototype.placeholder = function ()
{
    if (this.data.value === null || this.data.value === '') {
        this.element.innerHTML = '<span class="glyphicon glyphicon-edit"></span>';
    } else {
        this.element.innerHTML = this.data.value;
    }
};

UpdateAndReconcile.prototype.close = function ()
{
    this.editing = false;

    this.placeholder();
};

UpdateAndReconcile.prototype.getInputValue = function ()
{
    return this.parseValue(this.input.value);
};

UpdateAndReconcile.prototype.parseValue = function (value)
{
    if (this.type === 'int') {
        return parseInt(value, 10);
    }

    if (this.type === 'float') {
        return parseFloat(value);
    }

    return value;
};

UpdateAndReconcile.prototype.save = function ()
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
                if (this.selectorToReconcile !== null) {
                    [].forEach.call(document.querySelectorAll(this.selectorToReconcile), function (element) {
                        element.innerHTML = this.data.value;
                        var previousJsonData = element.getAttribute('data-update-and-reconcile');

                        if (previousJsonData !== null) {
                            var previousData = JSON.parse(previousJsonData);
                            previousData.value = this.data.value;

                            element.setAttribute('data-update-and-reconcile', JSON.stringify(previousData));
                        }
                    }.bind(this))
                }
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

UpdateAndReconcile.prototype.revert = function ()
{
    this.data.value = this.old;
    this.close();
};

UpdateAndReconcile.prototype.keyupped = function (event)
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

export default UpdateAndReconcile;
