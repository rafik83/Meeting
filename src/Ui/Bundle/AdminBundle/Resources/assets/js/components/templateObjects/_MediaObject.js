import Form from './../template/_Form';

/**
 * MediaObject
 *
 * @param element
 * @param locale
 * @param builderType
 *
 * @constructor
 */
function MediaObject(element, locale, builderType)
{
  this.element = element;
  this.locale = locale;
  this.form = new Form(element);
  this.config = JSON.parse(this.element.getAttribute('data-config'));
  this.builderType = builderType;
}

MediaObject.prototype.fill = function ()
{
  this.form.set('label', this.config.label[this.locale]);
  this.form.set('titlePlaceholder', this.config.titlePlaceholder[this.locale]);
  this.form.set('linkPlaceholder', this.config.linkPlaceholder[this.locale]);
  this.form.set('translatable', this.config.translatable);
  this.form.set('max', this.config.max);
  this.form.set('default', this.config.default);
  this.form.set('products', this.config.products);

  this.form.bind('label', this.config.label[this.locale]);
};

MediaObject.prototype.save = function ()
{
  this.config.label[this.locale]            = this.form.get('label');
  this.config.titlePlaceholder[this.locale] = this.form.get('titlePlaceholder');
  this.config.linkPlaceholder[this.locale]  = this.form.get('linkPlaceholder');
  this.config.translatable                  = this.form.get('translatable');
  this.config.max                           = this.form.get('max');
  this.config.default                       = this.form.get('default');
  this.config.products                      = this.form.get('products');

  this.form.bind('label', this.config.label[this.locale]);

  return true;
};

export default MediaObject;
