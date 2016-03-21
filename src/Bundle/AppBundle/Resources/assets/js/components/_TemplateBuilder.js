
var Sortable = require('./_Sortable');

function TemplateBuilder(element)
{
    this.element    = element;
    this.menu       = element.querySelector('#template-menu');
    this.openButton = element.querySelector('#template-menu-button');
    this.open       = false;
    this.drag       = false;

    this.openButton.addEventListener('click', function (event) {
        event.preventDefault();
        this.toggleMenu();
    }.bind(this));

    var blockList = element.querySelector('#block-list');

    new Sortable(blockList, {
        group: { name: 'block-reference', pull: 'clone', put: false },
        sort: false,
        onStart: function () {
            this.closeMenu();
            this.drag = true;
        }.bind(this),
        onEnd: function () {
            this.openMenu();
            this.drag = false;
        }.bind(this)
    });

    var templateContainer = element.querySelector('#template-container');

    this.sortable(templateContainer);
}

TemplateBuilder.prototype.toggleMenu = function (open)
{
    if (open !== undefined && this.open === open) {
        return;
    }

    this.menu.classList.toggle('slide-menu-container-open');
    this.openButton.querySelector('i').classList.toggle('glyphicon-chevron-left');
    this.openButton.querySelector('i').classList.toggle('glyphicon-chevron-right');
    this.open = !this.open;
};

TemplateBuilder.prototype.openMenu = function ()
{
    this.toggleMenu(true);
};

TemplateBuilder.prototype.closeMenu = function ()
{
    this.toggleMenu(false);
};

TemplateBuilder.prototype.sortable = function (element)
{
    new Sortable(element, {
        group: { name: 'block-list', pull: true, put: ['block-reference', 'block-inner'] },
        handle: '.move-button',
        onStart: function () {
            this.closeMenu();
            this.drag = true;
        }.bind(this),
        onEnd: function () {
            this.openMenu();
            this.drag = false;
        }.bind(this),
        onAdd: function (event) {

            if (event.item.parentNode === event.from) {
                return;
            }

            this.block(event.item);

        }.bind(this)
    });
};

TemplateBuilder.prototype.block = function (element)
{
    [].forEach.call(element.querySelectorAll('.block-inner'), function (inner) {
        this.sortable(inner);
    }.bind(this));

    element.querySelector('.delete-button').addEventListener('click', function (event) {
        event.preventDefault();
        element.remove();
    });
};

module.exports = TemplateBuilder;
