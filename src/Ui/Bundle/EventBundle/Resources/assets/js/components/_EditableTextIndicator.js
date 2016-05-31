function EditableTextIndicator(element, maxIndicator)
{
  this.element    = element;
  this.max        = maxIndicator;
  this.identifier = Math.random();

  this.element.addEventListener('keyup', function () {
    this.count();
  }.bind(this));
}

EditableTextIndicator.prototype.count = function ()
{
  var current = this.element.value.length;
  var remaining = this.max - current;

  if (remaining >= 0) {
    this.element.parentNode.removeChild(document.getElementById(this.identifier));
    this.element.parentNode.appendChild('<div id="' + this.identifier + '">' + remaining + ' caractères restants</div>');
  } else {
    this.element.parentNode.removeChild(document.getElementById(this.identifier));
    this.element.parentNode.appendChild('<div id="' + this.identifier + '">Nombre de caractères maximum atteint</div>');
  }
};

module.exports = EditableTextIndicator;
