var TemplateBlock = require('./_TemplateBlock');

/**
 * TemplateBuilder
 *
 * @param element
 * @constructor
 */
function RegistrationTemplateBuilder(element) {
  this.element = element;
  this.templateContainer = element.querySelector('#template-container');
  this.init(this.templateContainer);
}

RegistrationTemplateBuilder.prototype.init = function (element) {
  [].forEach.call(element.querySelectorAll('.block'), function (block) {
    this.block(block);
  }.bind(this));
};

RegistrationTemplateBuilder.prototype.block = function (element) {
  element.templateBlock = new TemplateBlock(element, this);
};

RegistrationTemplateBuilder.prototype.inners = function (item)
{
  return [].map.call(this.children(item.querySelector('.block-inner').parentNode.parentNode), function (column) {
    return column.querySelector('.block-inner');
  });
};

RegistrationTemplateBuilder.prototype.children = function (item)
{
  return [].filter.call(item.childNodes, function (child) {
    return child.nodeType === Node.ELEMENT_NODE;
  });
};

RegistrationTemplateBuilder.prototype.closeMenu = function ()
{
};

module.exports = RegistrationTemplateBuilder;
