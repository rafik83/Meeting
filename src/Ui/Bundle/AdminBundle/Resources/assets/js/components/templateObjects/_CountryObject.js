var Form = require('./../_Form'),
  TemplateTaggableObject = require('./../_TemplateTaggableObject');

/**
 * PhoneObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function CountryObject(element, locale)
{
  this.element = element;
  this.locale = locale;
  this.form = new Form(element);
  this.config = JSON.parse(this.element.getAttribute('data-config'));
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
  this.form.set('tag', this.config.tag);
  this.form.set('tags', this.config.tags);

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
  this.config.tag                      = this.form.get('tag');
  this.config.tags                     = this.form.get('tags');

  this.form.bind('label', this.config.label[this.locale]);

  return true;
};

module.exports = CountryObject;
