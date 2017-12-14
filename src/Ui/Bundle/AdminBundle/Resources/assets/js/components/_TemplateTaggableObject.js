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
    this.toggleDataType('sheet_tags', this.sheetTag, checked);
    this.toggleDisplay(this.sheetTagNode, checked);

    return;
  }

  this.toggleDataType('participant_tags', this.participantTag, checked);
  this.toggleDisplay(this.participantTagNode, checked);
};

TemplateTaggableObject.prototype.toggleDataType = function (dataTypeTagsName, dataTypeTag, checked)
{
  var tags = this.form.get('tags');

  if (checked) {
    // add tag
    if (-1 !== tags.indexOf(dataTypeTag)) {
      return;
    }

    tags.push(dataTypeTag);
    this.form.set('tags', tags);

    return;
  }

  // Remove tag
  tags = this.removeTagFromTags(dataTypeTag, tags);

  var dataTypeTags = this.form.get(dataTypeTagsName);

  dataTypeTags.forEach(function(tag) {
    tags = this.removeTagFromTags(tag, tags);
  }.bind(this));

  this.form.set('tags', tags);

  // reset data type tags
  this.form.set(dataTypeTagsName, []);
};

TemplateTaggableObject.prototype.removeTagFromTags = function (tag, tags)
{
  var tagIndex = tags.indexOf(tag);

  if (-1 !== tagIndex) {
    tags.splice(tagIndex, 1);
  }

  return tags;
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
