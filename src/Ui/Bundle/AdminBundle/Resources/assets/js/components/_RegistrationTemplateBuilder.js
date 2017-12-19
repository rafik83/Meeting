var LoadingButton = require('./_LoadingButton'),
    TemplateBlock = require('./_TemplateBlock'),
    guidGenerator = require('./_GuidGenerator'),
    normalizeTemplate = require('./_NormalizeTemplate'),
    Sortable = require('./_Sortable'),
    TemplateObject = require('./_TemplateObject');

/**
 * TemplateBuilder
 *
 * @param element
 * @constructor
 */
function RegistrationTemplateBuilder(element) {
  this.element = element;
  this.url = element.getAttribute('data-registration-template-builder');
  this.locale = element.getAttribute('data-locale');
  this.templateContainer = element.querySelector('#template-container');

  this.addBlockTemplate = element.querySelector('[data-add-block-template]');
  this.blockTemplate = element.querySelector('[data-block-template]');
  this.addBlockTemplate.style.display = 'none';
  this.blockTemplate.style.display = 'none';

  var saveButton = element.querySelector('#template-save-button');
  this.saveButton = new LoadingButton(saveButton, saveButton.getAttribute('data-loading-button'));
  this.saveButton.element.addEventListener('click', function (event) {
    event.preventDefault();
    this.save();
  }.bind(this));

  // Objects
  this.objectList = element.querySelector('#object-list');
  this.list(this.objectList, 'object-reference');

  this.init(this.templateContainer);

  document.addEventListener('template.block.beforeMovedUp', this.beforeMoveBlock.bind(this));
  document.addEventListener('template.block.afterMovedUp', this.afterMoveBlock.bind(this));

  document.addEventListener('template.block.beforeMovedDown', this.beforeMoveBlock.bind(this));
  document.addEventListener('template.block.afterMovedDown', this.afterMoveBlock.bind(this));

  document.addEventListener('template.block.afterRemoved', this.afterRemovedBlock.bind(this));
}

RegistrationTemplateBuilder.prototype.addAddButtons = function (element) {
  [].forEach.call(element.querySelectorAll('.block'), function (block) {
    this.addAddBlockButton(block);
  }.bind(this));
};

RegistrationTemplateBuilder.prototype.removeAddButtons = function (element) {
  [].forEach.call(element.querySelectorAll('[data-add-block]'), function (button) {
    button.remove();
  }.bind(this));
};

RegistrationTemplateBuilder.prototype.init = function (element) {
  [].forEach.call(element.querySelectorAll('.block'), function (block) {
    this.block(block);
  }.bind(this));

  [].forEach.call(element.querySelectorAll('.object'), function (object) {
      this.object(object);
  }.bind(this));

  this.addAddButtons(element);
};

RegistrationTemplateBuilder.prototype.addAddBlockButton = function (beforeElement) {
  var addBlock = this.addBlockTemplate.cloneNode(true);
  addBlock.style.display = 'block';
  addBlock.removeAttribute('data-add-block-template');
  addBlock.setAttribute('data-add-block', '');
  beforeElement.parentNode.insertBefore(addBlock, beforeElement);

  addBlock.addEventListener('click', function (event) {
    event.preventDefault();
    this.addBlock(addBlock);
  }.bind(this));
};

RegistrationTemplateBuilder.prototype.addBlock = function (beforeElement) {
  var uid = guidGenerator();
  var block = this.blockTemplate.children[0].cloneNode(true);
  block.style.display = 'block';
  block.setAttribute('data-uid', uid);
  beforeElement.parentNode.insertBefore(block, beforeElement);
  this.block(block);
  this.refreshAddButtons();
};

RegistrationTemplateBuilder.prototype.refreshAddButtons = function () {
  this.removeAddButtons(this.element);
  this.addAddButtons(this.element);
};

RegistrationTemplateBuilder.prototype.block = function (element) {
  element.templateBlock = new TemplateBlock(element, this);
};

RegistrationTemplateBuilder.prototype.object = function (element) {
  element.templateObject = new TemplateObject(element, this.locale);
};

RegistrationTemplateBuilder.prototype.inners = function (item) {
  return [].map.call(this.children(item.querySelector('.block-inner').parentNode.parentNode), function (column) {
    return column.querySelector('.block-inner');
  });
};

RegistrationTemplateBuilder.prototype.children = function (item) {
  return [].filter.call(item.childNodes, function (child) {
    return child.nodeType === Node.ELEMENT_NODE;
  });
};

RegistrationTemplateBuilder.prototype.closeMenu = function () {
  // nothing to do; closeMenu() is needed by TemplateBlock
};

RegistrationTemplateBuilder.prototype.save = function () {
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

RegistrationTemplateBuilder.prototype.normalize = function (item)
{
  return normalizeTemplate(this, item);
};

RegistrationTemplateBuilder.prototype.list = function (element, name)
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

RegistrationTemplateBuilder.prototype.addObject = function (element)
{
  // Dispatch DOM added element event
  document.dispatchEvent(new CustomEvent('dom.element.added', { 'detail': { 'element': element } }));

  // Enable object behavior
  this.object(element);

  // Open configure modal
  element.templateObject.openConfigureModal();
};

RegistrationTemplateBuilder.prototype.afterRemovedBlock = function (block) {
  // refresh add buttons
  this.refreshAddButtons(this.element);
};

RegistrationTemplateBuilder.prototype.beforeMoveBlock = function (block) {
  // remove add buttons in order to handle block move
  this.removeAddButtons(this.element);
};

RegistrationTemplateBuilder.prototype.afterMoveBlock = function (block) {
  // re-add buttons after block is moved
  this.addAddButtons(this.element);
};

module.exports = RegistrationTemplateBuilder;
