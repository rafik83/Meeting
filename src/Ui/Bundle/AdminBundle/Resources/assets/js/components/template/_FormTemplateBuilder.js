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
 * @param builderType
 * @constructor
 */
function FormTemplateBuilder(element, builderType) {
    this.element = element;
    this.builderType = builderType;
    this.url = element.getAttribute('data-save-url-template-builder');
    this.locale = element.getAttribute('data-locale');
    this.templateContainer = element.querySelector('.template-container');

    this.addBlockTemplate = element.querySelector('[data-template-add-block]');
    this.blockTemplate = element.querySelector('[data-template-block]');

    var saveButton = element.querySelector('#template-save-button');
    this.saveButton = new LoadingButton(saveButton, saveButton.getAttribute('data-loading-button'));
    this.saveButton.element.addEventListener('click', function (event) {
        event.preventDefault();
        this.save();
    }.bind(this));

    // Objects
    this.objectList = element.querySelector('#object-list');
    this.list(this.objectList, 'object-reference');

    this.init();

    document.addEventListener('template.block.beforeMovedUp', this.beforeMoveBlock.bind(this));
    document.addEventListener('template.block.afterMovedUp', this.afterMoveBlock.bind(this));

    document.addEventListener('template.block.beforeMovedDown', this.beforeMoveBlock.bind(this));
    document.addEventListener('template.block.afterMovedDown', this.afterMoveBlock.bind(this));

    document.addEventListener('template.block.afterRemoved', this.afterRemovedBlock.bind(this));
}

FormTemplateBuilder.prototype.addAddButtons = function () {
    [].forEach.call(this.templateContainer.querySelectorAll('.block'), function (block) {
        this.addAddBlockButton(block, 'before');
    }.bind(this));

    this.addAddBlockButton(this.templateContainer, 'append');
};

FormTemplateBuilder.prototype.removeAddButtons = function () {
    [].forEach.call(this.templateContainer.querySelectorAll('[data-add-block]'), function (button) {
        button.remove();
    }.bind(this));
};

FormTemplateBuilder.prototype.init = function () {
    [].forEach.call(this.templateContainer.querySelectorAll('.block'), function (block) {
        this.block(block);
    }.bind(this));

    [].forEach.call(this.templateContainer.querySelectorAll('.object'), function (object) {
        this.object(object);
    }.bind(this));

    this.addAddButtons();
};

FormTemplateBuilder.prototype.addAddBlockButton = function (element, position) {
    var addBlock = this.addBlockTemplate.cloneNode(true);
    addBlock.classList.remove('hide');
    addBlock.removeAttribute('data-template-add-block');
    addBlock.setAttribute('data-add-block', '');

    if ('before' === position) {
        element.parentNode.insertBefore(addBlock, element);
    } else if ('append' === position) {
        element.appendChild(addBlock);
    } else {
        throw new Error('Given position must be "before" or "append"');
    }

    addBlock.addEventListener('click', function (event) {
        event.preventDefault();
        this.addBlock(addBlock);
    }.bind(this));
};

FormTemplateBuilder.prototype.addBlock = function (beforeElement) {
    var uid = guidGenerator();
    var block = this.blockTemplate.children[0].cloneNode(true);
    block.setAttribute('data-uid', uid);
    beforeElement.parentNode.insertBefore(block, beforeElement);
    this.block(block);
    this.refreshAddButtons();
    block.scrollIntoView(true);
};

FormTemplateBuilder.prototype.refreshAddButtons = function () {
    this.removeAddButtons();
    this.addAddButtons();
};

FormTemplateBuilder.prototype.block = function (element) {
    element.templateBlock = new TemplateBlock(element, this);
};

FormTemplateBuilder.prototype.object = function (element) {
    element.templateObject = new TemplateObject(element, this.locale, this.builderType);
};

FormTemplateBuilder.prototype.inners = function (item) {
    return [].map.call(this.children(item.querySelector('.block-inner').parentNode.parentNode), function (column) {
        return column.querySelector('.block-inner');
    });
};

FormTemplateBuilder.prototype.children = function (item) {
    return [].filter.call(item.childNodes, function (child) {
        return child.nodeType === Node.ELEMENT_NODE;
    });
};

FormTemplateBuilder.prototype.closeMenu = function () {
    // nothing to do; closeMenu() is needed by TemplateBlock
};

FormTemplateBuilder.prototype.save = function () {
    this.saveButton.start();

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        var DONE = 4;
        var OK   = 200;

        if (xhr.readyState === DONE) {
            if (xhr.status === OK) {
            } else {
                var response = JSON.parse(xhr.response);
                alert(response.error);
            }

            this.saveButton.stop();
        }
    }.bind(this);

    xhr.open('POST', this.url);
    xhr.send(JSON.stringify(this.normalize(this.templateContainer)));
};

FormTemplateBuilder.prototype.normalize = function (item)
{
    return normalizeTemplate(this, item);
};

FormTemplateBuilder.prototype.list = function (element, name)
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
            this.drag = false;
        }.bind(this)
    });
};

FormTemplateBuilder.prototype.addObject = function (element)
{
    // Dispatch DOM added element event
    document.dispatchEvent(new CustomEvent('dom.element.added', { 'detail': { 'element': element } }));

    // Enable object behavior
    this.object(element);

    // Open configure modal
    element.templateObject.openConfigureModal();
};

FormTemplateBuilder.prototype.afterRemovedBlock = function (event) {
    this.refreshAddButtons();
};

FormTemplateBuilder.prototype.beforeMoveBlock = function (event) {
    // remove add buttons in order to handle block move
    this.removeAddButtons();
};

FormTemplateBuilder.prototype.afterMoveBlock = function (event) {
    // re-add buttons after block is moved
    this.addAddButtons();
};

export default FormTemplateBuilder;
