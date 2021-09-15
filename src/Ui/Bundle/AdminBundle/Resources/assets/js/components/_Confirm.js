
function Confirm(element)
{
    this.element = element;
    this.message = element.getAttribute('data-confirm');

    if (element.tagName === 'FORM') {
        this.element.addEventListener('submit', this.onClick.bind(this));
    } else {
        this.element.addEventListener('click', this.onClick.bind(this));
    }
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

export default Confirm;
