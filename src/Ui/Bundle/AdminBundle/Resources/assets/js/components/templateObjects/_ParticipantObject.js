var Form = require('./../_Form');

/**
 * ParticipantObject
 *
 * @param element
 * @param locale
 * @constructor
 */
function ParticipantObject(element, locale)
{
  this.element = element;
  this.locale  = locale;
  this.form    = new Form(element);
  this.config  = JSON.parse(this.element.getAttribute('data-config'));
}

ParticipantObject.prototype.fill = function ()
{
  this.form.set('style', this.config.style);
  this.form.set('label', this.config.label[this.locale]);
  this.form.set('numberOfParticipantShown', this.config.numberOfParticipantShown);

  this.form.bind('participant', this.config.label[this.locale] + ' ' + this.config.numberOfParticipantShown);
};

ParticipantObject.prototype.save = function ()
{
  this.config.style                    = this.form.get('style');
  this.config.label[this.locale]       = this.form.get('label');
  this.config.numberOfParticipantShown = this.form.get('numberOfParticipantShown');

  this.form.bind('participant', this.config.label[this.locale] + ' ' + this.config.numberOfParticipantShown);
};

module.exports = ParticipantObject;
