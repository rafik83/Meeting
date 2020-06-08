import LoadingButton from "../_LoadingButton";
import TemplateBlock from "./_TemplateBlock";
import guidGenerator from "./_GuidGenerator";
import normalizeTemplate from "./_NormalizeTemplate";
import Sortable from "./../_Sortable";
import TemplateObject from './_TemplateObject';

/**
 * TemplateBuilder
 *
 * @param element
 * @constructor
 */
function TemplateBuilder(element, builderType)
{
    this.element = element;
    this.builderType = builderType;
    this.url = element.getAttribute('data-template-builder');
    this.menu = element.querySelector('#template-menu');
    this.openButton = element.querySelector('#template-menu-button');
    this.locale = element.getAttribute('data-locale');
    this.wasOpen = false;
    this.open = false;
    this.drag = false;
    this.current = null;

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
    this.sortable(this.templateContainer);

    // Init
    this.init(this.templateContainer);
}

TemplateBuilder.prototype.init = function (element)
{
    [].forEach.call(element.querySelectorAll('.block'), function (block) {
        this.block(block);
    }.bind(this));

    [].forEach.call(element.querySelectorAll('.object'), function (object) {
        this.object(object);
    }.bind(this));
};

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
    this.wasOpen = this.open;

    this.toggleMenu(false);
};

TemplateBuilder.prototype.list = function (element, name)
{
    new Sortable(element, {
        group: { name: name, pull: 'clone', put: false },
        sort: false,
        onStart: function (event) {
            this.closeMenu();
            this.drag = true;
            var uid = guidGenerator();
            event.item.innerHTML = event.item.innerHTML.replace(new RegExp('__UID__', 'g'), uid);
            event.item.setAttribute('data-uid', uid);
        }.bind(this),
        onEnd: function () {
            this.openMenu();
            this.drag = false;
        }.bind(this)
    });
};

TemplateBuilder.prototype.sortable = function (element)
{
    new Sortable(element, {
        group: { name: 'block-list', pull: true, put: ['block-reference', 'object-reference', 'block-inner'] },
        handle: '.move-button',
        onStart: function () {
            this.closeMenu();
            this.drag = true;
        }.bind(this),
        onEnd: function () {
            if (this.wasOpen) {
                this.openMenu();
            }

            this.drag = false;
        }.bind(this),
        onAdd: function (event) {

            if (event.item.parentNode === event.from) {
                return;
            }

            if (event.from === this.blockList) {
                this.addBlock(event.item);
            }

            if (event.from === this.objectList) {
                this.addObject(event.item);
            }

        }.bind(this)
    });
};

TemplateBuilder.prototype.addBlock = function (element)
{
    // Dispatch DOM added element event
    document.dispatchEvent(new CustomEvent('dom.element.added', { 'detail': { 'element': element } }));

    // Enable block behavior
    this.block(element);

    // Open configure modal
    if (element.templateBlock.isObjectsCollection()) {
        element.templateBlock.fill();
        element.templateBlock.openConfigureModal();
    }
};

TemplateBuilder.prototype.addObject = function (element)
{
    // Dispatch DOM added element event
    document.dispatchEvent(new CustomEvent('dom.element.added', { 'detail': { 'element': element } }));

    // Enable object behavior
    this.object(element);

    // Open configure modal
    element.templateObject.openConfigureModal();
};

TemplateBuilder.prototype.block = function (element)
{
    // Create block
    element.templateBlock = new TemplateBlock(element, this, this.locale);
};

TemplateBuilder.prototype.object = function (element)
{
    // Create object
    element.templateObject = new TemplateObject(element, this.locale, this.builderType);
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
                var config = JSON.parse(xhr.response);
            } else {
                var response = JSON.parse(xhr.response);
                alert(response.error ? response.error : 'Error !');
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
    return normalizeTemplate(this, item);
};

export default TemplateBuilder;
