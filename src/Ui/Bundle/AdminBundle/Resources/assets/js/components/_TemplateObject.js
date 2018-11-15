var $ = require('jquery'),
  TextObject = require('./templateObjects/_TextObject'),
  EditableTextObject = require('./templateObjects/_EditableTextObject'),
  ButtonLinkObject = require('./templateObjects/_ButtonLinkObject'),
  ParticipantObject = require('./templateObjects/_ParticipantObject'),
  ImageObject = require('./templateObjects/_ImageObject'),
  TagObject = require('./templateObjects/_TagObject'),
  CollectionObject = require('./templateObjects/_CollectionObject'),
  NomenclatureObject = require('./templateObjects/_NomenclatureObject'),
  MediaObject = require('./templateObjects/_MediaObject'),
  TagsObject = require('./templateObjects/_TagsObject'),
  PhoneObject = require('./templateObjects/_PhoneObject'),
  CountryObject = require('./templateObjects/_CountryObject'),
  UrlObject = require('./templateObjects/_UrlObject'),
  BooleanObject = require('./templateObjects/_BooleanObject'),
  GenderObject = require('./templateObjects/_GenderObject'),
  UploadObject = require('./templateObjects/_UploadObject'),
  DatetimeObject = require('./templateObjects/_DatetimeObject')
;

/**
 * Template Object
 *
 * @param element
 * @param locale
 * @param builderType the type of builder use to build the object (registration, sheet, print)
 *
 * @constructor
 */
function TemplateObject(element, locale, builderType)
{
  this.element         = element;
  this.locale          = locale;
  this.configureModal  = element.querySelector('.configure-modal');
  this.saveButton      = this.configureModal.querySelector('.save-configuration');
  this.deleteButton    = element.querySelector('.delete-button');
  this.configureButton = element.querySelector('.configure-button');
  this.type            = element.getAttribute('data-object');
  this.builderType     = builderType;

  // UID
  this.uid = element.getAttribute('data-uid');

  // Init modal
  $(this.configureModal).modal({show: false});

  // Buttons
  this.deleteButton.addEventListener('click', this.deleteButtonClicked.bind(this));
  this.configureButton.addEventListener('click', this.configureButtonClicked.bind(this));
  if (null !== this.saveButton) {
    this.saveButton.addEventListener('click', this.saveButtonClicked.bind(this));
  }

  // Object
  if (this.type === 'text') {
    this.object = new TextObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'editable-text') {
    this.object = new EditableTextObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'button-link') {
    this.object = new ButtonLinkObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'participant') {
    this.object = new ParticipantObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'image') {
    this.object = new ImageObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'tag') {
    this.object = new TagObject(this.uid, this.element, this.locale, this.builderType);
  } else if (this.type === 'collection') {
    this.object = new CollectionObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'nomenclature') {
    this.object = new NomenclatureObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'media') {
    this.object = new MediaObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'tags') {
    this.object = new TagsObject(this.uid, this.element, this.locale, this.builderType);
  } else if (this.type === 'telephone') {
    this.object = new PhoneObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'country') {
    this.object = new CountryObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'url') {
    this.object = new UrlObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'boolean') {
    this.object = new BooleanObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'gender') {
    this.object = new GenderObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'upload') {
    this.object = new UploadObject(this.element, this.locale, this.builderType);
  } else if (this.type === 'datetime') {
    this.object = new DatetimeObject(this.element, this.locale, this.builderType);
  }

  this.object.fill();
}

TemplateObject.prototype.deleteButtonClicked = function (event)
{
  event.preventDefault();
  this.element.remove();
};

TemplateObject.prototype.configureButtonClicked = function (event)
{
  event.preventDefault();
  this.object.fill();
  this.openConfigureModal();
};

TemplateObject.prototype.saveButtonClicked = function (event)
{
  event.preventDefault();

  if (this.object.save()) {
    this.closeConfigureModal();
  }
};

TemplateObject.prototype.openConfigureModal = function ()
{
  $(this.configureModal).modal('show');
};

TemplateObject.prototype.closeConfigureModal = function ()
{
  $(this.configureModal).modal('hide');
};

TemplateObject.prototype.getConfig = function ()
{
  return this.object.config;
};

TemplateObject.prototype.normalize = function ()
{
  return {
    component: 'object',
    type: this.type,
    config: this.object.config
  };
};

module.exports = TemplateObject;
