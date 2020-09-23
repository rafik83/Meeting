import Form from './../template/_Form';

/**
 * TextObject
 *
 * @param element
 * @param locale
 * @param builderType
 * @constructor
 */
function TextObject(element, locale, builderType)
{
  this.element = element;
  this.locale  = locale;
  this.form    = new Form(element);
  this.config  = JSON.parse(this.element.getAttribute('data-config'));
  this.builderType = builderType;
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

export default TextObject;
