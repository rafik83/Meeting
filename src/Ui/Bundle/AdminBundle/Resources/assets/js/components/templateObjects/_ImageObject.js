var Form = require('./../_Form');

/**
 * ImageObject
 *
 * @param element
 * @param locale
 * @param builderType
 * @constructor
 */
function ImageObject(element, locale, builderType)
{
  this.element = element;
  this.locale = locale;
  this.form = new Form(element);
  this.config = JSON.parse(this.element.getAttribute('data-config'));
  this.builderType = builderType;
}

ImageObject.prototype.fill = function ()
{
  this.form.set('style', this.config.style);
  this.form.set('label', this.config.label[this.locale]);
  this.form.set('placeholder', this.config.placeholder[this.locale]);
  this.form.set('help', this.config.help[this.locale]);
  this.form.set('required', this.config.required);
  this.form.set('products', this.config.products);

  this.form.bind('label', this.config.label[this.locale]);
};

ImageObject.prototype.save = function ()
{
  this.config.style                    = this.form.get('style');
  this.config.label[this.locale]       = this.form.get('label');
  this.config.placeholder[this.locale] = this.form.get('placeholder');
  this.config.help[this.locale]        = this.form.get('help');
  this.config.required                 = this.form.get('required');
  this.config.products                 = this.form.get('products');

  this.form.bind('label', this.config.label[this.locale]);

  return true;
};

module.exports = ImageObject;
