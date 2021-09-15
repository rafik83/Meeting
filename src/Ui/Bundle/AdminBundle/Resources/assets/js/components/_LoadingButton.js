
function LoadingButton(element, placeholder)
{
    this.element     = element;
    this.origin      = element.innerHTML;
    this.placeholder = placeholder;
}

LoadingButton.prototype.start = function ()
{
    this.element.innerHTML = this.placeholder;
    this.element.setAttribute('disabled', 'disabled');
    this.element.classList.add('disabled');
};

LoadingButton.prototype.stop = function ()
{
    this.element.innerHTML = this.origin;
    this.element.removeAttribute('disabled');
    this.element.classList.remove('disabled');
};

export default LoadingButton;
