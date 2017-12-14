var Form = require('./_Form');

/**
 * @param element
 * @constructor
 */
function TemplateTaggableObject(element)
{
  var dataTemplateTagsSelectElement = element.querySelector('[data-template-tags-select]');

  if (!dataTemplateTagsSelectElement) {
    return;
  }

  this.atLeastOneDataFieldMustBeCheckedMessage = dataTemplateTagsSelectElement.getAttribute('data-at-least-one-data-field-must-be-checked-message');
  this.participantTag = dataTemplateTagsSelectElement.getAttribute('data-participant-tag');
  this.sheetTag = dataTemplateTagsSelectElement.getAttribute('data-sheet-tag');

  this.sheetDataField = element.querySelector('input[name="' + this.sheetTag + '"]');
  this.participantDataField = element.querySelector('input[name="' + this.participantTag + '"]');
  this.sheetDataField.onchange = this.handleDataTypeChanged.bind(this);
  this.participantDataField.onchange = this.handleDataTypeChanged.bind(this);

  this.sheetTagNode = element.querySelector('[data-sheet-node]');
  this.participantTagNode = element.querySelector('[data-participant-node]');

  this.form = new Form(dataTemplateTagsSelectElement);

  if (!this.form.get(this.sheetTag)) {
    this.toggleDisplay(this.sheetTagNode, false);
  }

  if (!this.form.get(this.participantTag)) {
    this.toggleDisplay(this.participantTagNode, false);
  }
}

TemplateTaggableObject.prototype.handleDataTypeChanged = function (event)
{
  var checked = event.target.checked;

  if (event.target === this.sheetDataField) {
    this.toggleDataType(this.sheetDataField, this.sheetTag, checked);
    this.toggleDisplay(this.sheetTagNode, checked);

    return;
  }

  this.toggleDataType(this.participantDataField, this.participantTag, checked);
  this.toggleDisplay(this.participantTagNode, checked);
};

TemplateTaggableObject.prototype.toggleDataType = function (element, tag, checked)
{
  var tags = this.form.get('tags');
  var tagIndex = tags.indexOf(tag);

  if (checked) {
    // add tag
    if (-1 !== tagIndex) {
      return;
    }

    tags.push(tag);
    this.form.set('tags', tags);

    return;
  }

  if (-1 !== tagIndex) {
    // remove tag
    tags.splice(tagIndex, 1);
    this.form.set('tags', tags);
  }
};

TemplateTaggableObject.prototype.toggleDisplay = function (element, displayed)
{
  element.style.display = displayed ? 'block' : 'none';
};

TemplateTaggableObject.prototype.save = function ()
{
  var atLeastOneDataFieldIsChecked = this.sheetDataField.checked || this.participantDataField.checked;

  if (!atLeastOneDataFieldIsChecked) {
    alert(this.atLeastOneDataFieldMustBeCheckedMessage);
  }

  return atLeastOneDataFieldIsChecked;
};

module.exports = TemplateTaggableObject;
