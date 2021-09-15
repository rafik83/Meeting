import $ from 'jquery';
import Form from './../template/_Form';

/**
 * TagObject
 *
 * @param uid
 * @param element
 * @param locale
 * @param builderType
 * @constructor
 */
function TagObject(uid, element, locale, builderType)
{
  this.uid = uid;
  this.element = element;
  this.locale = locale;
  this.form = new Form(element);
  this.config = JSON.parse(this.element.getAttribute('data-config'));
  this.builderType = builderType;

  [].forEach.call(this.element.querySelectorAll('[data-collection-bind]'), function (element) {
    element.setAttribute('data-collection', element.getAttribute('data-collection-bind'));
    $(element).collection();
  });
}

TagObject.prototype.fill = function ()
{
  this.form.set('style', this.config.style);
  this.form.set('label', this.config.label[this.locale]);
  this.form.set('tag', this.config.tag);

  [].forEach.call(this.config.tags, function (tag, index) {
    this.form.set('tags[' + index + '][tag]', this.config.tags[index].tag);
  }.bind(this));

  this.form.bind('label', this.config.label[this.locale]);
};

TagObject.prototype.save = function ()
{
  this.config.style              = this.form.get('style');
  this.config.label[this.locale] = this.form.get('label');

  var indexes = [];

  [].forEach.call(this.element.querySelectorAll('.tags-item-' + this.uid), function (element) {
    var index = parseInt(element.getAttribute('data-index'));
    indexes.push(index);

    if (this.config.tags[index] === undefined) {
      this.config.tags[index] = {
        tag: null
      }
    }

    this.config.tags[index].tag = this.form.get('tags[' + index + '][tag]');
  }.bind(this));

  var tags = [];

  [].forEach.call(this.config.tags, function (tag, index) {
    if (-1 !== indexes.indexOf(index)) {
      tags.push(tag);
    }
  }.bind(this));

  this.config.tags = tags;

  this.form.bind('label', this.config.label[this.locale]);

  return true;
};

export default TagObject;
