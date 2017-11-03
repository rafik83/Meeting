var LoadingButton = require('./_LoadingButton'),
    Sortable      = require('./_Sortable'),
    TemplateBlock = require('./_TemplateBlock');

/**
 * PrintTemplateBuilder
 *
 * @param element
 * @constructor
 */
function PrintTemplateBuilder(element) {
  this.element = element;
  this.url = element.getAttribute('data-template-builder');
  this.menu = element.querySelector('#template-menu');
  this.openButton = element.querySelector('#template-menu-button');
  this.locale = element.getAttribute('data-locale');
  this.wasOpen = false;
  this.open = false;
  this.drag = false;
  this.current = null;

  var saveButton = element.querySelector('#template-save-button');
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
  this.updateObjectList();

  // Template
  this.templateContainer = element.querySelector('#template-container');
  this.sortable(this.templateContainer);

  // Init
  this.init(this.templateContainer);

  this.emptyObjectListLabel = element.querySelector('#empty-object-list-label');
  this.updateEmptyObjectListLabel();

  document.addEventListener('print.template.object.removed', function (event) {
    this.removeObject(event.detail.element);
  }.bind(this));
}

PrintTemplateBuilder.prototype.updateObjectList = function () {
  this.list(this.objectList, 'object-reference');
};

PrintTemplateBuilder.prototype.removeObject = function (element) {
  this.objectList.appendChild(element);
  this.updateObjectList();
  this.updateEmptyObjectListLabel();
};

PrintTemplateBuilder.prototype.updateEmptyObjectListLabel = function () {
  if (this.objectList.hasChildNodes()) {
    this.emptyObjectListLabel.style.display = 'none';
    return;
  }

  this.emptyObjectListLabel.style.display = 'block';
};

PrintTemplateBuilder.prototype.init = function (element) {
  [].forEach.call(element.querySelectorAll('.block'), function (block) {
    this.block(block);
  }.bind(this));

  [].forEach.call(element.querySelectorAll('.object'), function (object) {
    this.object(object);
  }.bind(this));
};

PrintTemplateBuilder.prototype.toggleMenu = function (open) {
  if (open !== undefined && this.open === open) {
    return;
  }

  this.menu.classList.toggle('slide-menu-container-open');
  this.openButton.querySelector('i').classList.toggle('glyphicon-chevron-left');
  this.openButton.querySelector('i').classList.toggle('glyphicon-chevron-right');
  this.open = !this.open;
};

PrintTemplateBuilder.prototype.openMenu = function () {
  this.toggleMenu(true);
};

PrintTemplateBuilder.prototype.closeMenu = function () {
  this.wasOpen = this.open;

  this.toggleMenu(false);
};

PrintTemplateBuilder.prototype.list = function (element, name) {
  new Sortable(element, {
    group: {name: name, pull: true, put: false},
    sort: false,
    onStart: function (event) {
      this.closeMenu();
      this.drag = true;
    }.bind(this),
    onEnd: function () {
      this.drag = false;
      this.updateEmptyObjectListLabel();
      this.openMenu();
    }.bind(this)
  });
};

PrintTemplateBuilder.prototype.sortable = function (element) {
  new Sortable(element, {
    group: {name: 'block-list', pull: true, put: ['block-reference', 'object-reference', 'block-inner']},
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

PrintTemplateBuilder.prototype.addBlock = function (element) {
  // Dispatch DOM added element event
  document.dispatchEvent(new CustomEvent('dom.element.added', {'detail': {'element': element}}));

  // Enable block behavior
  this.block(element);
};

PrintTemplateBuilder.prototype.addObject = function (element) {
  // Dispatch DOM added element event
  document.dispatchEvent(new CustomEvent('dom.element.added', {'detail': {'element': element}}));

  // Enable object behavior
  this.object(element);
};

PrintTemplateBuilder.prototype.block = function (element) {
  // Create block
  element.templateBlock = new TemplateBlock(element, this);
};

PrintTemplateBuilder.prototype.object = function (element) {
  // Create object
  element.templateObject = new PrintTemplateObject(element, this.locale);
};

PrintTemplateBuilder.prototype.save = function () {
  this.saveButton.start();

  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function () {
    var DONE = 4;
    var OK = 200;

    if (xhr.readyState === DONE) {
      if (xhr.status === OK) {
        var config = JSON.parse(xhr.response);
      } else {
        alert('error');
      }

      this.saveButton.stop();
    }
  }.bind(this);
  xhr.open('POST', this.url);
  xhr.send(JSON.stringify(this.normalize(this.templateContainer)));
};

PrintTemplateBuilder.prototype.inners = function (item) {
  return [].map.call(this.children(item.querySelector('.block-inner').parentNode.parentNode), function (column) {
    return column.querySelector('.block-inner');
  });
};

PrintTemplateBuilder.prototype.children = function (item) {
  return [].filter.call(item.childNodes, function (child) {
    return child.nodeType === Node.ELEMENT_NODE;
  });
};

PrintTemplateBuilder.prototype.normalize = function (item) {
  var blockType = item.getAttribute('data-block');
  var objectType = item.getAttribute('data-object');

  if (blockType !== null && blockType !== undefined) {
    return {
      component: 'block',
      type: blockType,
      children: [].map.call(this.inners(item), function (child) {
        return this.normalize(child);
      }.bind(this)),
      config: item.templateBlock.config
    }
  }

  if (objectType !== null && objectType !== undefined) {
    return item.templateObject.normalize();
  }

  var config = {};

  [].forEach.call(this.children(item), function (child) {
    var template = child.templateBlock || child.templateObject;
    config[template.uid] = this.normalize(child);
  }.bind(this));

  return config;
};

/**
 * Print Template Object
 *
 * @param element
 * @param locale
 * @constructor
 */
function PrintTemplateObject(element, locale) {
  this.element = element;
  this.locale = locale;
  this.deleteButton = element.querySelector('.delete-button');
  this.type = element.getAttribute('data-object');
  this.config = JSON.parse(this.element.getAttribute('data-config'));

  // UID
  this.uid = element.getAttribute('data-uid');

  // Buttons
  this.deleteButton.addEventListener('click', this.deleteButtonClicked.bind(this));
}

PrintTemplateObject.prototype.deleteButtonClicked = function (event) {
  event.preventDefault();
  document.dispatchEvent(new CustomEvent('print.template.object.removed', {'detail': {'element': this.element}}));
};

PrintTemplateObject.prototype.getConfig = function () {
  return this.object.config;
};

PrintTemplateObject.prototype.normalize = function () {
  return {
    component: 'object',
    type: this.type,
    config: this.object.config
  };
};

module.exports = PrintTemplateBuilder;
