function TypeDescription(element)
{
    this.element            = element;
    this.description        = element.getAttribute('data-description');
    this.descriptionElement = document.getElementById('type-description');

    this.element.addEventListener('change', this.onChange.bind(this));
}

TypeDescription.prototype.onChange = function (event)
{
    if ('' != this.description) {
        this.descriptionElement.innerHTML = this.description;
        this.descriptionElement.classList.remove('hide');

        return;
    }

    this.descriptionElement.classList.add('hide');
};

module.exports = TypeDescription;
