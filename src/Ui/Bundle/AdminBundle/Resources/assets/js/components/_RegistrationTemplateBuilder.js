var LoadingButton = require('./_LoadingButton'),
  TemplateBlock = require('./_TemplateBlock');

/**
 * TemplateBuilder
 *
 * @param element
 * @constructor
 */
function RegistrationTemplateBuilder(element) {
  this.element = element;
  this.templateContainer = element.querySelector('#template-container');

  var saveButton = element.querySelector('#template-save-button');
  this.saveButton = new LoadingButton(saveButton, saveButton.getAttribute('data-loading-button'));
  this.saveButton.element.addEventListener('click', function (event) {
    event.preventDefault();
    this.save();
  }.bind(this));

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
        var config = JSON.parse(xhr.response);
      } else {
        alert('Error');
      }

      this.saveButton.stop();
    }
  }.bind(this);

  xhr.open('POST', this.url);
  xhr.send(JSON.stringify(this.normalize(this.templateContainer)));
};

module.exports = RegistrationTemplateBuilder;
