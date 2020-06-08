import $ from 'jquery';
import Form from './_Form';
import Sortable from './../_Sortable';

/**
 * Template Block
 *
 * @param element
 * @param builder
 * @param locale
 * @constructor
 */
function TemplateBlock(element, builder, locale)
{
  this.element = element;
  this.inner = element.querySelector('.block-inner');
  this.locale = locale;
  this.builder = builder;
  this.config = JSON.parse(this.element.getAttribute('data-config'));
  this.configureModal = element.querySelector('.configure-modal');
  this.configureButton = element.querySelector('.configure-button');
  this.upButton = element.querySelector('.up-button');
  this.downButton = element.querySelector('.down-button');

  if (this.configureModal) {
    this.form = new Form(this.configureModal);
    this.saveButton = this.configureModal.querySelector('.save-configuration');
  }

  // UID
  this.uid = element.getAttribute('data-uid');

  // Init modal
  $(this.configureModal).modal({show: false});

  // Init block inner as sortable target
  [].forEach.call(this.builder.inners(element), function (inner) {
    this.sortable(inner);
  }.bind(this));

  // Delete button behavior
  this.element.querySelector('.delete-button').addEventListener('click', function (event) {
    event.preventDefault();
    if (confirm(this.element.querySelector('.delete-button').getAttribute('data-confirmation-message'))) {
      document.dispatchEvent(new CustomEvent('template.block.beforeRemoved', {'detail': {'element': this.element}}));
      this.element.remove();
      document.dispatchEvent(new CustomEvent('template.block.afterRemoved'));
    }
  }.bind(this));

  // Modal behavior
  if (this.configureModal) {
    this.configureButton.addEventListener('click', this.configureButtonClicked.bind(this));
  }

  if (this.saveButton) {
    this.saveButton.addEventListener('click', this.saveButtonClicked.bind(this));
  }

  // Up and down buttons behavior if they exists
  if (null !== this.upButton) {
    this.upButton.addEventListener('click', this.upButtonClicked.bind(this));
  }

  if (null !== this.downButton) {
    this.downButton.addEventListener('click', this.downButtonClicked.bind(this));
  }
}

TemplateBlock.prototype.upButtonClicked = function (event)
{
  event.preventDefault();

  if (null !== this.element.previousElementSibling) {
    document.dispatchEvent(new CustomEvent('template.block.beforeMovedUp', {'detail': {'element': this.element}}));
    this.element.parentNode.insertBefore(this.element, this.element.previousElementSibling);
    document.dispatchEvent(new CustomEvent('template.block.afterMovedUp', {'detail': {'element': this.element}}));
  }
};

TemplateBlock.prototype.downButtonClicked = function (event)
{
  event.preventDefault();

  if (null !== this.element.nextElementSibling) {
    document.dispatchEvent(new CustomEvent('template.block.beforeMovedDown', {'detail': {'element': this.element}}));
    this.element.parentNode.insertBefore(this.element.nextElementSibling, this.element);
    document.dispatchEvent(new CustomEvent('template.block.afterMovedDown', {'detail': {'element': this.element}}));
  }
};

TemplateBlock.prototype.configureButtonClicked = function (event)
{
  event.preventDefault();
  this.fill();
  this.openConfigureModal();
};

TemplateBlock.prototype.saveButtonClicked = function (event)
{
  event.preventDefault();
  this.save();
  this.closeConfigureModal();
};

TemplateBlock.prototype.openConfigureModal = function ()
{
  $(this.configureModal).modal('show');
};

TemplateBlock.prototype.closeConfigureModal = function ()
{
  $(this.configureModal).modal('hide');
};

TemplateBlock.prototype.fill = function ()
{
  this.form.set('style', this.config.style);

  if (this.isObjectsCollection()) {
      this.form.set('label', this.config.label[this.locale]);
      this.form.set('maxItems', this.config.maxItems);
  }
};

TemplateBlock.prototype.save = function ()
{
  this.config.style = this.form.get('style');

  if (this.isObjectsCollection()) {
      var maxItems = parseInt(this.form.get('maxItems'));
      this.config.label[this.locale] = this.form.get('label');
      this.config.maxItems = maxItems > 0 ? maxItems : 10;
  }
};

TemplateBlock.prototype.isObjectsCollection = function (element)
{
  if (!element) {
    element = this.inner;
  }

  return element.classList.contains('block-collection');
};

TemplateBlock.prototype.sortable = function (element)
{
  var elementsCanBeAdded = ['block-reference', 'object-reference', 'block-inner'];

  if (this.isObjectsCollection(element)) {
      elementsCanBeAdded = ['object-reference'];
  }

  new Sortable(element, {
    group: { name: 'block-list', pull: true, put: elementsCanBeAdded },
    handle: '.move-button',
    onStart: function () {
      this.builder.closeMenu();
      this.builder.drag = true;
    }.bind(this),
    onEnd: function () {
      if (this.builder.wasOpen) {
        this.builder.openMenu();
      }

      this.builder.drag = false;
    }.bind(this),
    onAdd: function (event) {
      if (event.item.parentNode === event.from) {
        return;
      }

      if (event.from === this.builder.blockList) {
        this.builder.addBlock(event.item);
      }

      if (event.from === this.builder.objectList) {
        this.builder.addObject(event.item);
      }
    }.bind(this)
  });
};

export default TemplateBlock;
