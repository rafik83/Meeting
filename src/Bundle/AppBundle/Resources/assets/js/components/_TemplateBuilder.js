
var Sortable = require('./_Sortable');

function TemplateBuilder(element)
{
    this.element    = element;
    this.menu       = element.querySelector('.slide-menu-container');
    this.openButton = element.querySelector('.slide-menu-button');

    this.openButton.addEventListener('click', function (event) {
        event.preventDefault();
        this.menu.classList.toggle('slide-menu-container-open');
        this.openButton.querySelector('i').classList.toggle('glyphicon-chevron-left');
        this.openButton.querySelector('i').classList.toggle('glyphicon-chevron-right');
    }.bind(this));
}

module.exports = TemplateBuilder;
