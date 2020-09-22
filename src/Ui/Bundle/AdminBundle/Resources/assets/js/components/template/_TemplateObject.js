import $ from 'jquery';
import TextObject from "../templateObjects/_TextObject";
import EditableTextObject from "../templateObjects/_EditableTextObject";
import ButtonLinkObject from "../templateObjects/_ButtonLinkObject";
import ParticipantObject from "../templateObjects/_ParticipantObject";
import ImageObject from "../templateObjects/_ImageObject";
import TagObject from "../templateObjects/_TagObject";
import CollectionObject from "../templateObjects/_CollectionObject";
import NomenclatureObject from "../templateObjects/_NomenclatureObject";
import MediaObject from "../templateObjects/_MediaObject";
import TagsObject from "../templateObjects/_TagsObject";
import PhoneObject from "../templateObjects/_PhoneObject";
import UrlObject from "../templateObjects/_UrlObject";
import BooleanObject from "../templateObjects/_BooleanObject";
import CountryObject from "../templateObjects/_CountryObject";
import GenderObject from "../templateObjects/_GenderObject";
import UploadObject from "../templateObjects/_UploadObject";
import DatetimeObject from "../templateObjects/_DatetimeObject";
import MultiUploadObject from "../templateObjects/_MultiUploadObject";
import VideoObject from "../templateObjects/_VideoObject";

/**
 * Template Object
 *
 * @param element
 * @param locale
 * @param builderType the type of builder use to build the object (registration, form, sheet, print)
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
  switch (this.type) {
    case 'text':
      this.object = new TextObject(this.element, this.locale, this.builderType);
      break;
    case 'editable-text':
      this.object = new EditableTextObject(this.element, this.locale, this.builderType);
      break;
    case 'button-link':
      this.object = new ButtonLinkObject(this.element, this.locale, this.builderType);
      break;
    case 'participant':
      this.object = new ParticipantObject(this.element, this.locale, this.builderType);
      break;
    case 'image':
      this.object = new ImageObject(this.element, this.locale, this.builderType);
      break;
    case 'tag':
      this.object = new TagObject(this.uid, this.element, this.locale, this.builderType);
      break;
    case 'collection':
      this.object = new CollectionObject(this.element, this.locale, this.builderType);
      break;
    case 'nomenclature':
      this.object = new NomenclatureObject(this.element, this.locale, this.builderType);
      break;
    case 'media':
      this.object = new MediaObject(this.element, this.locale, this.builderType);
      break;
    case 'tags':
      this.object = new TagsObject(this.uid, this.element, this.locale, this.builderType);
      break;
    case 'telephone':
      this.object = new PhoneObject(this.element, this.locale, this.builderType);
      break;
    case 'country':
      this.object = new CountryObject(this.element, this.locale, this.builderType);
      break;
    case 'url':
      this.object = new UrlObject(this.element, this.locale, this.builderType);
      break;
    case 'boolean':
      this.object = new BooleanObject(this.element, this.locale, this.builderType);
      break;
    case 'gender':
      this.object = new GenderObject(this.element, this.locale, this.builderType);
      break;
    case 'upload':
      this.object = new UploadObject(this.element, this.locale, this.builderType);
      break;
    case 'datetime':
      this.object = new DatetimeObject(this.element, this.locale, this.builderType);
      break;
    case 'multi-upload':
      this.object = new MultiUploadObject(this.element, this.locale, this.builderType);
      break;
    case 'video':
      this.object = new VideoObject(this.element, this.locale, this.builderType);
      break;
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

export default TemplateObject;
