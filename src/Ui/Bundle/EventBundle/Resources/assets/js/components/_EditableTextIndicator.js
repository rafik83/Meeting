function EditableTextIndicator(element, maxIndicator, maxIndicatorTranslations)
{
  this.element    = element;
  this.max        = maxIndicator;
  this.identifier = Math.random();

  this.maxIndicatorArea = document.createElement('div');
  this.maxIndicatorArea.classList.add('help-block');
  this.element.parentNode.insertBefore(this.maxIndicatorArea, this.element.nextSibling);

  var translations = maxIndicatorTranslations.split('|');
  this.maxIndicatorRemainingPluralTranslation   = translations[0];
  this.maxIndicatorRemainingSingularTranslation = translations[1];
  this.maxIndicatorReachedTranslation           = translations[2];

  this.element.addEventListener('keyup', function () {
    this.count();
  }.bind(this));
  this.count();
}

EditableTextIndicator.prototype.count = function ()
{
  var current   = this.element.value.replace(/(\r\n|\n|\r)/g, '--').length;
  var remaining = this.max - current;

  if (remaining > 1) {
    this.element.parentElement.classList.remove('has-error');
    this.maxIndicatorArea.innerHTML = remaining + ' ' + this.maxIndicatorRemainingPluralTranslation;
  } else if (remaining === 1) {
    this.element.parentElement.classList.remove('has-error');
    this.maxIndicatorArea.innerHTML = remaining + ' ' + this.maxIndicatorRemainingSingularTranslation;
  } else {
    this.element.parentElement.classList.add('has-error');
    this.maxIndicatorArea.innerHTML = this.maxIndicatorReachedTranslation;
  }
};

export default EditableTextIndicator;
