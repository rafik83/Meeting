import Form from "./_Form";

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

  this.sheetTagsFieldName = 'sheet_tags';
  this.participantTagsFieldName = 'participant_tags';

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
    this.toggleDisplay(this.sheetTagNode, checked);

    return;
  }

  this.toggleDisplay(this.participantTagNode, checked);
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
    return false
  }

  var tags = [];

  if (this.sheetDataField.checked) {
    tags = tags.concat(this.sheetTag, this.form.get(this.sheetTagsFieldName));
  }

  if (this.participantDataField.checked) {
    tags = tags.concat(this.participantTag, this.form.get(this.participantTagsFieldName));
  }

  this.form.set('tags', tags);

  return true;
};

export default TemplateTaggableObject;
