function ShowMore(element, buttonContainer)
{
    this.element   = element;
    this.showState = false;
    this.maxHeight = 86;

    this.link = document.createElement('a');
    this.link.setAttribute('href', '#');
    this.link.classList.add('link-show-more');

    if (buttonContainer.querySelector('.edit-link') != null) {
        this.link.style.float = 'right';
    }

    if (this.element.clientHeight > this.maxHeight) {
        buttonContainer.appendChild(this.link);
        this.hide();
    }

    this.link.addEventListener('click', this.toggle.bind(this), false);
}

ShowMore.prototype.hide = function()
{
    this.element.classList.add("show-less");
    this.link.innerHTML = this.getIcon() + this.element.getAttribute('data-show-less');
    this.showState      = false;
};

ShowMore.prototype.show = function()
{
    this.element.classList.remove("show-less");
    this.link.innerHTML = this.getIcon() + this.element.getAttribute('data-show-more');
    this.showState      = true;
};

ShowMore.prototype.getIcon = function()
{
    return '<i class="icon-Voir_1"></i> ';
};

ShowMore.prototype.toggle = function(event)
{
    event.preventDefault();
    event.stopPropagation();

    this.showState ? this.hide() : this.show();
};

export default ShowMore;
