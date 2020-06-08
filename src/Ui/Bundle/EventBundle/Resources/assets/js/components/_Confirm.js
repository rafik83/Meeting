function Confirm(element)
{
    this.element = element;
    this.message = element.getAttribute('data-confirm');
    this.element.addEventListener('click', this.onClick.bind(this));
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
