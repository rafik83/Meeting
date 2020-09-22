import Form from './../template/_Form';

/**
 * ButtonLinkObject
 *
 * @param element
 * @param locale
 * @param builderType
 * @constructor
 */
function ButtonLinkObject(element, locale, builderType)
{
  this.element = element;
  this.locale = locale;
  this.form = new Form(element);
  this.config = JSON.parse(this.element.getAttribute('data-config'));
  this.builderType = builderType;
}

ButtonLinkObject.prototype.fill = function ()
{
  this.form.set('style', this.config.style);
  this.form.set('label', this.config.label[this.locale]);
  this.form.set('help', this.config.help[this.locale]);
  this.form.set('required', this.config.required);

  this.form.bind('link', this.config.label[this.locale]);
};

ButtonLinkObject.prototype.save = function ()
{
  this.config.style              = this.form.get('style');
  this.config.label[this.locale] = this.form.get('label');
  this.config.help[this.locale]  = this.form.get('help');
  this.config.required           = this.form.get('required');

  this.form.bind('link', this.config.label[this.locale]);

  return true;
};

export default ButtonLinkObject;
