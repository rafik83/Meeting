var $        = require('jquery'),
    Form     = require('./_Form'),
    Sortable = require('./_Sortable');

/**
 * Template Block
 *
 * @param element
 * @param builder
 * @constructor
 */
function TemplateBlock(element, builder)
{
  this.element         = element;
  this.builder         = builder;
  this.config          = JSON.parse(this.element.getAttribute('data-config'));
  this.configureModal  = element.querySelector('.configure-modal');
  this.configureButton = element.querySelector('.configure-button');
  this.form            = new Form(this.configureModal);
  this.saveButton      = this.configureModal.querySelector('.save-configuration');

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
    document.dispatchEvent(new CustomEvent('template.block.removed', {'detail': {'element': this.element}}));
    this.element.remove();
  }.bind(this));

  // Modal behavior
  this.configureButton.addEventListener('click', this.configureButtonClicked.bind(this));
  this.saveButton.addEventListener('click', this.saveButtonClicked.bind(this));
}

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
};

TemplateBlock.prototype.save = function ()
{
  this.config.style = this.form.get('style');
};

TemplateBlock.prototype.sortable = function (element)
{
  new Sortable(element, {
    group: { name: 'block-list', pull: true, put: ['block-reference', 'object-reference', 'block-inner'] },
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

module.exports = TemplateBlock;
