var Form = require('./../_Form');

/**
 * TextObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function TextObject(element, locale)
{
  this.element = element;
  this.locale  = locale;
  this.form    = new Form(element);
  this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

TextObject.prototype.fill = function ()
{
  this.form.set('style', this.config.style);
  if (this.config.content) {
    this.form.set('content', this.config.content[this.locale]);
    this.form.bind('content', this.config.content[this.locale]);
  }
  this.form.set('type', this.config.type);

};

TextObject.prototype.save = function ()
{
  this.config.style                = this.form.get('style');
  this.config.content[this.locale] = this.form.get('content');
  this.config.type                 = this.form.get('type');

  this.form.bind('content', this.config.content[this.locale]);

  return true;
};

module.exports = TextObject;
