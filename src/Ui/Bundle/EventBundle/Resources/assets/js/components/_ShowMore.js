
function ShowMore(element)
{
    this.element   = element;
    this.maxHeight = 65;
    this.showState = false;

    // create show/hide link
    this.link = document.createElement('a');
    this.link.setAttribute('href', '#');
    this.link.style.float = 'right';

    if (this.element.clientHeight > this.maxHeight) {
        this.hide();
        this.element.parentNode.querySelector('footer').appendChild(this.link);
    }

    this.link.addEventListener('click', this.toggle.bind(this), false);
}

ShowMore.prototype.hide = function()
{
    this.element.style.maxHeight = '' + this.maxHeight + 'px';
    this.element.style.overflow  = "hidden";
    this.link.innerHTML          = 'Voir plus';
    this.showState               = false;
};

ShowMore.prototype.show = function()
{
    this.element.style.MaxHeight = 'auto';
    this.element.style.overflow  = "visible";
    this.link.innerHTML          = 'Voir moins';
    this.showState               = true;
};

ShowMore.prototype.toggle = function(event)
{
    event.preventDefault();
    event.stopPropagation();

    this.showState ? this.hide() : this.show();
};

module.exports = ShowMore;
