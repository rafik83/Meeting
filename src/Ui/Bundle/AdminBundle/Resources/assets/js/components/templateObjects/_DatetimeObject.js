var Form = require('./../_Form'),
    TemplateTaggableObject = require('./../_TemplateTaggableObject')
;

/**
 * DatetimeObject
 *
 * @param element
 * @param locale
 * @param builderType
 *
 * @constructor
 */
function DatetimeObject(element, locale, builderType)
{
    this.element = element;
    this.locale = locale;
    this.form = new Form(element);
    this.config = JSON.parse(this.element.getAttribute('data-config'));
    this.builderType = builderType;

    this.templateTaggableObject = null;

    if (element.querySelector('[data-template-tags-select]')) {
        this.templateTaggableObject = new TemplateTaggableObject(element);
    }
}

DatetimeObject.prototype.fill = function ()
{
    this.form.set('help', this.config.help[this.locale]);
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('required', this.config.required);
    this.form.set('tags', this.config.tags);
    this.form.set('type', this.config.type);
    this.form.set('datetime_min', this.config.datetime_min);
    this.form.set('datetime_max', this.config.datetime_max);

    this.form.bind('label', this.config.label[this.locale]);
};

DatetimeObject.prototype.save = function ()
{
    if (this.templateTaggableObject && !this.templateTaggableObject.save()) {
        return false;
    }

    this.config.label[this.locale] = this.form.get('label');
    this.config.help               = this.form.get('help');
    this.config.required           = this.form.get('required');
    this.config.tags               = this.form.get('tags');
    this.config.type               = this.form.get('type');
    this.config.datetime_min       = this.form.get('datetime_min');
    this.config.datetime_max       = this.form.get('datetime_max');

    this.form.bind('label', this.config.label[this.locale]);

    return true;
};

module.exports = DatetimeObject;
