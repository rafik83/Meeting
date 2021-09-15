import Form from './../template/_Form';
import TemplateTaggableObject from './../template/_TemplateTaggableObject';

/**
 * CountryObject
 *
 * @param element
 * @param locale
 * @param builderType
 *
 * @constructor
 */
function CountryObject(element, locale, builderType)
{
  this.element = element;
  this.locale = locale;
  this.form = new Form(element);
  this.config = JSON.parse(this.element.getAttribute('data-config'));
  this.builderType = builderType;
  this.templateTaggableObject = null;

  if (element.querySelector('[data-template-tags-select]')) {
    this.templateTaggableObject = new TemplateTaggableObject(element);
  }
}

CountryObject.prototype.fill = function ()
{
  this.form.set('label', this.config.label[this.locale]);
  this.form.set('placeholder', this.config.placeholder[this.locale]);
  this.form.set('required', this.config.required);
  this.form.set('tags', this.config.tags);
  this.form.set('visibility', this.config.visibility);

  this.form.bind('label', this.config.label[this.locale]);
};

CountryObject.prototype.save = function ()
{
  if (this.templateTaggableObject && !this.templateTaggableObject.save()) {
    return false;
  }

  this.config.label[this.locale]       = this.form.get('label');
  this.config.placeholder[this.locale] = this.form.get('placeholder');
  this.config.required                 = this.form.get('required');
  this.config.tags                     = this.form.get('tags');
  this.config.visibility               = this.form.get('visibility');

  this.form.bind('label', this.config.label[this.locale]);

  return true;
};

export default CountryObject;
