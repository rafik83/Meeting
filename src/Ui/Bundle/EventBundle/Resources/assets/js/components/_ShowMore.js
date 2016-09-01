
function ShowMore(element, buttonContainer)
{
    this.element   = element;
    this.maxHeight = 86;
    this.showState = false;

    // create show/hide link
    this.link = document.createElement('a');
    this.link.setAttribute('href', '#');

    if (buttonContainer.querySelector('.edit-link') != null) {
        this.link.style.float = 'right';
    }

    if (this.element.clientHeight > this.maxHeight) {
        this.hide();
        buttonContainer.appendChild(this.link);
    }

    this.link.addEventListener('click', this.toggle.bind(this), false);
}

ShowMore.prototype.hide = function()
{
    this.element.style.maxHeight = '' + this.maxHeight + 'px';
    this.element.style.overflow  = "hidden";
    this.link.innerHTML          = this.element.getAttribute('data-show-less');
    this.showState               = false;
};

ShowMore.prototype.show = function()
{
    this.element.style.maxHeight = 'none';
    this.element.style.overflow  = "visible";
    this.link.innerHTML          = this.element.getAttribute('data-show-more');
    this.showState               = true;
};

ShowMore.prototype.toggle = function(event)
{
    event.preventDefault();
    event.stopPropagation();

    this.showState ? this.hide() : this.show();
};

module.exports = ShowMore;
