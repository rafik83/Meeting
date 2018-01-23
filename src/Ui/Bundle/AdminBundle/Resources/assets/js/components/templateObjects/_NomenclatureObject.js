var Form = require('./../_Form');

/**
 * NomenclatureObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function NomenclatureObject(element, locale)
{
  this.element = element;
  this.locale  = locale;
  this.form    = new Form(element);
  this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

NomenclatureObject.prototype.fill = function ()
{
  this.form.set('style', this.config.style);
  this.form.set('label', this.config.label[this.locale]);
  this.form.set('help', this.config.help[this.locale]);
  this.form.set('nomenclature', this.config.nomenclature);
  this.form.set('mode', this.config.mode);
  this.form.set('objective', this.config.objective);
  this.form.set('required', this.config.required);

  this.form.bind('label', this.config.label[this.locale]);
};

NomenclatureObject.prototype.save = function ()
{
  this.config.style              = this.form.get('style');
  this.config.label[this.locale] = this.form.get('label');
  this.config.help[this.locale]  = this.form.get('help');
  this.config.nomenclature       = this.form.get('nomenclature');
  this.config.mode               = this.form.get('mode');
  this.config.objective          = this.form.get('objective');
  this.config.required           = this.form.get('required');

  this.form.bind('label', this.config.label[this.locale]);

  return true;
};

module.exports = NomenclatureObject;
