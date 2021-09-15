import Form from './../template/_Form';
import TemplateTaggableObject from './../template/_TemplateTaggableObject';
import {isSheetTemplateBuilder} from '../template/_BuilderTypeChecker';

/**
 * NomenclatureObject
 *
 * @param element
 * @param locale
 * @param builderType
 *
 * @constructor
 */
function NomenclatureObject(element, locale, builderType)
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

NomenclatureObject.prototype.fill = function ()
{
  if (isSheetTemplateBuilder(this.builderType)) {
    this.form.set('style', this.config.style);
    this.form.set('objective', this.config.objective);
    this.form.set('help', this.config.help[this.locale]);
  }

  this.form.set('label', this.config.label[this.locale]);
  this.form.set('mode', this.config.mode);
  this.form.set('nomenclature', this.config.nomenclature);
  this.form.set('required', this.config.required);
  this.form.set('tags', this.config.tags);
  this.form.set('visibility', this.config.visibility);

  this.form.bind('label', this.config.label[this.locale]);
};

NomenclatureObject.prototype.save = function ()
{
  if (this.templateTaggableObject && !this.templateTaggableObject.save()) {
      return false;
  }

  if (isSheetTemplateBuilder(this.builderType)) {
    this.config.style = this.form.get('style');
    this.config.help[this.locale]  = this.form.get('help');
    this.config.objective = this.form.get('objective');
  }

  this.config.label[this.locale] = this.form.get('label');
  this.config.mode               = this.form.get('mode');
  this.config.nomenclature       = this.form.get('nomenclature');
  this.config.required           = this.form.get('required');
  this.config.tags               = this.form.get('tags');
  this.config.visibility         = this.form.get('visibility');

  this.form.bind('label', this.config.label[this.locale]);

  return true;
};

export default NomenclatureObject;
