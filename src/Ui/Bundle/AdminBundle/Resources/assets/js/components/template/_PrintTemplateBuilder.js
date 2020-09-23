import LoadingButton from "../_LoadingButton";
import TemplateBlock from "./_TemplateBlock";
import guidGenerator from "./_GuidGenerator";
import normalizeTemplate from "./_NormalizeTemplate";
import * as Sortable from "./../_Sortable";

/**
 * PrintTemplateBuilder
 *
 * @param element
 * @constructor
 */
function PrintTemplateBuilder(element) {
  this.element = element;
  this.url = element.getAttribute('data-print-template-builder');
  this.menu = element.querySelector('#template-menu');
  this.openButton = element.querySelector('#template-menu-button');
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
  this.listBlocks();

  // Objects
  this.objectList = element.querySelector('#object-list');
  this.listObjects();

  // Template
  this.templateContainer = element.querySelector('#template-container');
  this.sortable(this.templateContainer);

  // Init empty object list label
  this.emptyObjectListLabel = element.querySelector('#empty-object-list-label');
  this.updateEmptyObjectListLabel();

  // Init template
  this.init(this.templateContainer);

  document.addEventListener('print.template.object.removed', function (event) {
    this.removeObject(event.detail.element);
  }.bind(this));

  document.addEventListener('template.block.beforeRemoved', function (event) {
    this.removedBlock(event.detail.element);
  }.bind(this));
}

PrintTemplateBuilder.prototype.removedBlock = function (element) {
  [].forEach.call(element.querySelectorAll('.object'), function (object) {
    this.removeObject(object);
  }.bind(this));
};

PrintTemplateBuilder.prototype.removeObject = function (element) {
  this.objectList.appendChild(element);
  this.listObjects();
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

PrintTemplateBuilder.prototype.sortObjects = function () {
  var sortedObjectsList = [];

  // get object content
  [].forEach.call(this.objectList.querySelectorAll('.object'), function (object) {
    var objectContent = object.querySelector('[data-bind]');
    sortedObjectsList.push({'object': object, 'content': objectContent.textContent});
  }.bind(this));

  // sort by object content
  sortedObjectsList.sort(function (a, b) {
    return a.content.localeCompare(b.content);
  });

  // append sorted list
  sortedObjectsList.forEach(function (element) {
    this.objectList.appendChild(element.object);
  }.bind(this));
};

PrintTemplateBuilder.prototype.listObjects = function () {
  this.sortObjects();

  new Sortable(this.objectList, {
    group: {name: 'object-reference', pull: true, put: false},
    sort: false,
    onStart: function () {
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

PrintTemplateBuilder.prototype.listBlocks = function () {
  new Sortable(this.blockList, {
    group: {name: 'block-reference', pull: 'clone', put: false},
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
  element.templateObject = new PrintTemplateObject(element);
};

PrintTemplateBuilder.prototype.save = function () {
  this.saveButton.start();

  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function () {
    var DONE = 4;
    var OK = 200;

    if (xhr.readyState === DONE) {
      if (xhr.status !== OK) {
        alert('Error');
      }

      this.saveButton.stop();
    }
  }.bind(this);

  xhr.open('POST', this.url);
  var data = JSON.stringify(this.normalize(this.templateContainer));
  xhr.send(data);
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
  return normalizeTemplate(this, item);
};

/**
 * Print Template Object
 *
 * @param element
 * @constructor
 */
function PrintTemplateObject(element) {
  this.element = element;
  this.uid = element.getAttribute('data-uid');
  this.deleteButton = element.querySelector('.delete-button');
  this.deleteButton.addEventListener('click', this.deleteButtonClicked.bind(this));
}

PrintTemplateObject.prototype.deleteButtonClicked = function (event) {
  event.preventDefault();
  document.dispatchEvent(new CustomEvent('print.template.object.removed', {'detail': {'element': this.element}}));
};


PrintTemplateObject.prototype.normalize = function () {
  return {
    component: 'object'
  };
};

export default PrintTemplateBuilder;
