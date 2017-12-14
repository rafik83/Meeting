/**
 * @param element
 * @constructor
 */
function TemplateTaggableObject(element)
{
  this.sheetDataField = element.querySelector('input[name="sheet_data"]');

  if (!this.sheetDataField) {
    return;
  }

  this.participantDataField = element.querySelector('input[name="participant_data"]');
  this.sheetDataField.onchange = this.handleDataTypeChanged.bind(this);
  this.participantDataField.onchange = this.handleDataTypeChanged.bind(this);

  this.sheetTagNode = element.querySelector('[data-sheet-tag]');
  this.participantTagNode = element.querySelector('[data-participant-tag]');

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

module.exports = TemplateTaggableObject;
