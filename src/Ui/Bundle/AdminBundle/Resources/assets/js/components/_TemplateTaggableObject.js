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

  this.toggleDisplay(this.sheetTagNode, false);
  this.toggleDisplay(this.participantTagNode, false);
}

TemplateTaggableObject.prototype.handleDataTypeChanged = function (event)
{
  if (event.target === this.sheetDataField) {
    this.toggleDisplay(this.sheetTagNode, event.target.checked);

    return;
  }

  this.toggleDisplay(this.participantTagNode, event.target.checked);
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
