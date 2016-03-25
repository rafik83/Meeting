
var $             = require('jquery');
var Sortable      = require('./_Sortable');
var LoadingButton = require('./_LoadingButton');

/**
 * TemplateBuilder
 *
 * @param element
 * @constructor
 */
function TemplateBuilder(element)
{
    this.element    = element;
    this.url        = element.getAttribute('data-template-builder');
    this.menu       = element.querySelector('#template-menu');
    this.openButton = element.querySelector('#template-menu-button');
    this.open       = false;
    this.drag       = false;
    this.current    = null;

    var saveButton  = element.querySelector('#template-save-button');
    this.saveButton = new LoadingButton(saveButton, saveButton.getAttribute('data-loading-button'));

    // Open button
    this.openButton.addEventListener('click', function (event) {
        event.preventDefault();
        this.toggleMenu();
    }.bind(this));

    // Save button
    this.saveButton.element.addEventListener('click', function (event) {
        event.preventDefault();
        this.save();
    }.bind(this));

    // Blocks
    this.blockList = element.querySelector('#block-list');
    this.list(this.blockList, 'block-reference');

    // Objects
    this.objectList = element.querySelector('#object-list');
    this.list(this.objectList, 'object-reference');

    // Template
    this.templateContainer = element.querySelector('#template-container');
    this.sortable(this.templateContainer, ['block-reference', 'block-inner']);
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

TemplateBuilder.prototype.list = function (element, name)
{
    new Sortable(element, {
        group: { name: name, pull: 'clone', put: false },
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
    // Dispatch DOM added element event
    document.dispatchEvent(new CustomEvent('dom.element.added', { 'detail': { 'element': element } }));

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
    // Dispatch DOM added element event
    document.dispatchEvent(new CustomEvent('dom.element.added', { 'detail': { 'element': element } }));

    // Create object
    var templateObject = new TemplateObject(element);
    templateObject.openConfigureModal();
};

TemplateBuilder.prototype.save = function ()
{
    this.saveButton.start();

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        var DONE = 4;
        var OK   = 200;

        if (xhr.readyState === DONE) {
            if (xhr.status === OK) {

            } else {
                alert('error');
            }

            this.saveButton.stop();
        }
    }.bind(this);
    xhr.open('POST', this.url);
    xhr.send(JSON.stringify(this.normalize(this.templateContainer)));
};

TemplateBuilder.prototype.inners = function (item)
{
    return [].map.call(this.children(item.querySelector('.block-inner').parentNode.parentNode), function (column) {
        return column.querySelector('.block-inner');
    });
};

TemplateBuilder.prototype.children = function (item)
{
    return [].filter.call(item.childNodes, function (child) {
        return child.nodeType === Node.ELEMENT_NODE;
    });
};

TemplateBuilder.prototype.normalize = function (item)
{
    var blockType  = item.getAttribute('data-block');
    var objectType = item.getAttribute('data-object');

    if (blockType !== null && blockType !== undefined) {
        return {
            component: 'block',
            type: blockType,
            config: [].map.call(this.inners(item), function (child) {
                return this.normalize(child);
            }.bind(this))
        }
    }

    if (objectType !== null && objectType !== undefined) {
        return {
            component: 'object',
            type: objectType,
            config: item.templateObject.object.config
        }
    }

    return [].map.call(this.children(item), function (child) {
        return this.normalize(child);
    }.bind(this));
};

/**
 * Template Object
 *
 * @param element
 * @constructor
 */
function TemplateObject(element)
{
    element.templateObject = this;

    this.element         = element;
    this.configureModal  = element.querySelector('.configure-modal');
    this.saveButton      = this.configureModal.querySelector('.save-configuration');
    this.deleteButton    = element.querySelector('.delete-button');
    this.configureButton = element.querySelector('.configure-button');
    this.type            = element.getAttribute('data-object');

    // Init modal
    $(this.configureModal).modal({show: false});

    // Buttons
    this.deleteButton.addEventListener('click', this.deleteButtonClicked.bind(this));
    this.configureButton.addEventListener('click', this.configureButtonClicked.bind(this));
    this.saveButton.addEventListener('click', this.saveButtonClicked.bind(this));

    // Object
    if (this.type === 'text') {
        this.object = new TextObject(this.element);
    } else if (this.type === 'editable-text') {
        this.object = new EditableTextObject(this.element);
    }
}

TemplateObject.prototype.deleteButtonClicked = function (event)
{
    event.preventDefault();
    this.element.remove();
};

TemplateObject.prototype.configureButtonClicked = function (event)
{
    event.preventDefault();
    this.object.fill();
    this.openConfigureModal();
};

TemplateObject.prototype.saveButtonClicked = function (event)
{
    event.preventDefault();
    this.object.save();
    this.closeConfigureModal();
};

TemplateObject.prototype.openConfigureModal = function ()
{
    $(this.configureModal).modal('show');
};

TemplateObject.prototype.closeConfigureModal = function ()
{
    $(this.configureModal).modal('hide');
};

TemplateObject.prototype.getConfig = function ()
{
    return this.object.config;
};

/**
 * TextObject
 *
 * @param element
 * @constructor
 */
function TextObject(element)
{
    this.element = element;
    this.config  = { content: null, type: null };
}

TextObject.prototype.fill = function ()
{
    this.element.querySelector('textarea[name="content"]').value = this.config.content;
    this.element.querySelector('select[name="type"]').value      = this.config.type;
};

TextObject.prototype.save = function ()
{
    this.config.content = this.element.querySelector('textarea[name="content"]').value;
    this.config.type    = this.element.querySelector('select[name="type"]').value;

    this.element.querySelector('[data-bind="content"]').innerHTML = '' + this.config.content;
};

/**
 * EditableTextObject
 *
 * @param element
 * @constructor
 */
function EditableTextObject(element)
{
    this.element = element;
    this.config  = { content: null, type: null };
}

EditableTextObject.prototype.fill = function ()
{
    this.element.querySelector('textarea[name="content"]').value = this.config.content;
    this.element.querySelector('select[name="type"]').value      = this.config.type;
};

EditableTextObject.prototype.save = function ()
{
    this.config.content = this.element.querySelector('textarea[name="content"]').value;
    this.config.type    = this.element.querySelector('select[name="type"]').value;

    this.element.querySelector('[data-bind="content"]').innerHTML = '' + this.config.content;
};

module.exports = TemplateBuilder;
