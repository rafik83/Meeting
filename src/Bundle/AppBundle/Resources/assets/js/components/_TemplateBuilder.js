
var Sortable = require('./_Sortable');

function TemplateBuilder(element)
{
    this.element    = element;
    this.menu       = element.querySelector('#template-menu');
    this.openButton = element.querySelector('#template-menu-button');
    this.open       = false;
    this.drag       = false;

    // Open button

    this.openButton.addEventListener('click', function (event) {
        event.preventDefault();
        this.toggleMenu();
    }.bind(this));

    // Blocks

    this.blockList = element.querySelector('#block-list');

    new Sortable(this.blockList, {
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

    // Objects

    this.objectList = element.querySelector('#object-list');

    new Sortable(this.objectList, {
        group: { name: 'object-reference', pull: 'clone', put: false },
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

    // Template

    var templateContainer = element.querySelector('#template-container');

    this.sortable(templateContainer, ['block-reference', 'block-inner']);
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

TemplateBuilder.prototype.sortable = function (element, accept)
{
    new Sortable(element, {
        group: { name: 'block-list', pull: true, put: accept },
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

            if (event.from === this.blockList) {
                this.block(event.item);
            }

            if (event.from === this.objectList) {
                this.object(event.item);
            }

        }.bind(this)
    });
};

TemplateBuilder.prototype.block = function (element)
{
    // Init block inner as sortable target
    [].forEach.call(element.querySelectorAll('.block-inner'), function (inner) {
        this.sortable(inner, ['block-reference', 'object-reference', 'block-inner']);
    }.bind(this));

    // Delete button behavior
    [].forEach.call(element.querySelectorAll('.delete-button'), function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            element.remove();
        });
    });
};

TemplateBuilder.prototype.object = function (element)
{
    // Delete button behavior
    [].forEach.call(element.querySelectorAll('.delete-button'), function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            element.remove();
        });
    });
};

module.exports = TemplateBuilder;
