function ShowMoreParticipants(element)
{
    this.element        = element;
    this.showState      = false;
    this.showMoreButton = element.querySelector('.show-more-button');
    this.showMoreZone   = element.querySelector('.show-more-zone');

    if (this.showMoreButton !== null && this.showMoreZone !== null) {
        this.showMoreTranslation = this.showMoreButton.getAttribute('data-show-more-translation');
        this.showLessTranslation = this.showMoreButton.getAttribute('data-show-less-translation');

        this.showMoreButton.addEventListener('click', this.toggle.bind(this));
    }
}

ShowMoreParticipants.prototype.toggle = function(event)
{
    event.preventDefault();
    event.stopPropagation();

    this.showState ? this.hide() : this.show();
};

ShowMoreParticipants.prototype.hide = function()
{
    this.showMoreZone.style.display = "none";
    this.showState = false;
    this.showMoreButton.innerHTML = this.getIcon() + this.showMoreTranslation;
};

ShowMoreParticipants.prototype.show = function()
{
    this.showMoreZone.style.display = "block";
    this.showState = true;
    this.showMoreButton.innerHTML = this.getIcon() + this.showLessTranslation;
};

ShowMoreParticipants.prototype.getIcon = function()
{
    return '<i class="icon-Voir_1"></i> ';
};

export default ShowMoreParticipants;
