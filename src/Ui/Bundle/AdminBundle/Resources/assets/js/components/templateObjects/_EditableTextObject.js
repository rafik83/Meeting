var Form = require('./../_Form'),
  TemplateTaggableObject = require('./../_TemplateTaggableObject');

/**
 * EditableTextObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function EditableTextObject(element, locale)
{
  this.element = element;
  this.locale  = locale;
  this.form    = new Form(element);
  this.config  = JSON.parse(this.element.getAttribute('data-config'));

  this.sheetDataField = element.querySelector('input[name="sheet_data"]');
  this.templateTaggableObject = null;

  if (this.sheetDataField) {
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

  this.form.bind('label', this.config.label[this.locale]);
};

EditableTextObject.prototype.save = function ()
{
  if (this.templateTaggableObject) {
    alert('error');
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

  this.form.bind('label', this.config.label[this.locale]);

  return true;
};

module.exports = EditableTextObject;
