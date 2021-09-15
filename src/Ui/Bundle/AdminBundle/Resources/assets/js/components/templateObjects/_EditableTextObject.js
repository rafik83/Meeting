import Form from './../template/_Form';
import TemplateTaggableObject from './../template/_TemplateTaggableObject';

/**
 * EditableTextObject
 *
 * @param element
 * @param locale
 * @param builderType
 * @constructor
 */
function EditableTextObject(element, locale, builderType)
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

EditableTextObject.prototype.fill = function ()
{
  this.form.set('style', this.config.style);
  this.form.set('label', this.config.label[this.locale]);
  this.form.set('placeholder', this.config.placeholder[this.locale]);
  this.form.set('help', this.config.help[this.locale]);
  this.form.set('maxLength', this.config.maxLength);
  this.form.set('type', this.config.type);
  this.form.set('required', this.config.required);
  this.form.set('translatable', this.config.translatable);
  this.form.set('hideLabel', this.config.hideLabel);
  this.form.set('tag', this.config.tag);
  this.form.set('tags', this.config.tags);
  this.form.set('visibility', this.config.visibility);

  this.form.bind('label', this.config.label[this.locale]);
};

EditableTextObject.prototype.save = function ()
{
  if (this.templateTaggableObject && !this.templateTaggableObject.save()) {
    return false;
  }

  this.config.style                    = this.form.get('style');
  this.config.label[this.locale]       = this.form.get('label');
  this.config.placeholder[this.locale] = this.form.get('placeholder');
  this.config.help[this.locale]        = this.form.get('help');
  this.config.maxLength                = this.form.get('maxLength');
  this.config.type                     = this.form.get('type');
  this.config.required                 = this.form.get('required');
  this.config.translatable             = this.form.get('translatable');
  this.config.hideLabel                = this.form.get('hideLabel');
  this.config.tag                      = this.form.get('tag');
  this.config.tags                     = this.form.get('tags');
  this.config.visibility               = this.form.get('visibility');

  this.form.bind('label', this.config.label[this.locale]);

  return true;
};

export default EditableTextObject;
