import Form from './../template/_Form';

/**
 * CollectionObject
 *
 * @param element
 * @param locale
 * @param builderType
 *
 * @constructor
 */
function CollectionObject(element, locale, builderType)
{
  this.element = element;
  this.locale = locale;
  this.form = new Form(element);
  this.config = JSON.parse(this.element.getAttribute('data-config'));
  this.builderType = builderType;
}

CollectionObject.prototype.fill = function ()
{
  this.form.set('style', this.config.style);
  this.form.set('label', this.config.label[this.locale]);
  this.form.set('placeholder', this.config.placeholder[this.locale]);
  this.form.set('help', this.config.help[this.locale]);
  this.form.set('required', this.config.required);
  this.form.set('default', this.config.default);
  this.form.set('translatable', this.config.translatable);

  this.form.bind('label', this.config.label[this.locale]);
};

CollectionObject.prototype.save = function ()
{
  this.config.style                    = this.form.get('style');
  this.config.label[this.locale]       = this.form.get('label');
  this.config.placeholder[this.locale] = this.form.get('placeholder');
  this.config.help[this.locale]        = this.form.get('help');
  this.config.required                 = this.form.get('required');
  this.config.default                  = this.form.get('default');
  this.config.translatable             = this.form.get('translatable');

  this.form.bind('label', this.config.label[this.locale]);

  return true;
};

export default CollectionObject;
