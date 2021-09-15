import $ from 'jquery';
import Form from './../template/_Form';

/**
 * TagsObject
 *
 * @param uid
 * @param element
 * @param locale
 * @param builderType
 *
 * @constructor
 */
function TagsObject(uid, element, locale, builderType)
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

TagsObject.prototype.fill = function ()
{
  this.form.set('style', this.config.style);
  this.form.set('label', this.config.label[this.locale]);
  this.form.set('collection', this.config.collection);
  this.form.set('placeholder', this.config.placeholder[this.locale]);
  this.form.set('help', this.config.help[this.locale]);
  this.form.set('required', this.config.required);
  this.form.set('default', this.config.default);
  this.form.set('translatable', this.config.translatable);

  [].forEach.call(this.config.tags, function (tag, index) {
    this.form.set('tags[' + index + '][tag]', this.config.tags[index].tag);
    this.form.set('tags[' + index + '][label][' + this.locale + ']', this.config.tags[index].label[this.locale]);
  }.bind(this));

  this.form.bind('label', this.config.label[this.locale]);
};

TagsObject.prototype.save = function ()
{
  this.config.style                    = this.form.get('style');
  this.config.label[this.locale]       = this.form.get('label');
  this.config.collection               = this.form.get('collection');
  this.config.placeholder[this.locale] = this.form.get('placeholder');
  this.config.help[this.locale]        = this.form.get('help');
  this.config.required                 = this.form.get('required');
  this.config.default                  = this.form.get('default');
  this.config.translatable             = this.form.get('translatable');

  var indexes = [];

  [].forEach.call(this.element.querySelectorAll('.tags-item-' + this.uid), function (element) {
    var index = parseInt(element.getAttribute('data-index'));
    indexes.push(index);

    if (this.config.tags[index] === undefined) {
      this.config.tags[index] = {
        tag: null,
        label: {}
      }
    }

    this.config.tags[index].tag                = this.form.get('tags[' + index + '][tag]');
    this.config.tags[index].label[this.locale] = this.form.get('tags[' + index + '][label][' + this.locale + ']');

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

export default TagsObject;
