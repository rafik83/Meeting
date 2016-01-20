var $ = require('jquery');

function Confirm(element)
{
    this.element = $(element);
    this.message = $(element).data('confirm');
    this.element.on('click', this.onClick.bind(this));
}

Confirm.prototype.onClick = function (event)
{
    if (confirm(this.message)) {
        return true;
    } else {
        event.preventDefault();

        return false;
    }
};

module.exports = Confirm;
