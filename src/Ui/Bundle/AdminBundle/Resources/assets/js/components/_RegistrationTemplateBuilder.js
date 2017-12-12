var TemplateBlock = require('./_TemplateBlock'),
    guidGenerator = require('./_GuidGenerator'),
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
  this.templateContainer = element.querySelector('#template-container');

  // Objects
  this.objectList = element.querySelector('#object-list');
  this.list(this.objectList, 'object-reference');

  this.init(this.templateContainer);
}

RegistrationTemplateBuilder.prototype.init = function (element) {
  [].forEach.call(element.querySelectorAll('.block'), function (block) {
    this.block(block);
  }.bind(this));
  [].forEach.call(element.querySelectorAll('.object'), function (object) {
      this.object(object);
  }.bind(this));
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

RegistrationTemplateBuilder.prototype.addBlock = function (element)
{
  // Dispatch DOM added element event
  document.dispatchEvent(new CustomEvent('dom.element.added', { 'detail': { 'element': element } }));

  // Enable block behavior
  this.block(element);
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

module.exports = RegistrationTemplateBuilder;
