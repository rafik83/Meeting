var Form = require('./../_Form');

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

  if (this.sheetDataField) {
    this.participantDataField = element.querySelector('input[name="participant_data"]');
    this.sheetDataField.onchange = this.handleDataTypeChanged.bind(this);
    this.participantDataField.onchange = this.handleDataTypeChanged.bind(this);

    this.sheetTagNode = element.querySelector('[data-sheet-tag]');
    this.participantTagNode = element.querySelector('[data-participant-tag]');

    this.toggleDisplay(this.sheetTagNode, false);
    this.toggleDisplay(this.participantTagNode, false);
  }
}

EditableTextObject.prototype.handleDataTypeChanged = function (event)
{
  if (event.target === this.sheetDataField) {
    this.toggleDisplay(this.sheetTagNode, event.target.checked);

    return;
  }

  this.toggleDisplay(this.participantTagNode, event.target.checked);
};

EditableTextObject.prototype.toggleDisplay = function (element, displayed)
{
  element.style.display = displayed ? 'block' : 'none';
};

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
};

module.exports = EditableTextObject;
