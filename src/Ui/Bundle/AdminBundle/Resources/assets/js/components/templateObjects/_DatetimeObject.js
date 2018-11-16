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
    this.dateMinShouldBeGreaterThanDateMaxMessage = this.element.getAttribute('data-date-min-should-be-greater-than-date-max-message');

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
    this.form.set('format', this.config.format);
    this.form.set('datetime_min', this.config.datetime_min);
    this.form.set('datetime_max', this.config.datetime_max);

    this.form.bind('label', this.config.label[this.locale]);
};

DatetimeObject.prototype.save = function ()
{
    var dateMin = this.form.get('datetime_min');
    var dateMax = this.form.get('datetime_max');
    var minTime = this.getTimestampByInternationalFormat(dateMin);
    var maxTime = this.getTimestampByInternationalFormat(dateMax);

    if ((dateMin && dateMax) && minTime > maxTime) {
        alert(this.dateMinShouldBeGreaterThanDateMaxMessage);

        return false;
    }

    if (this.templateTaggableObject && !this.templateTaggableObject.save()) {
        return false;
    }

    this.config.label[this.locale] = this.form.get('label');
    this.config.help[this.locale]  = this.form.get('help');
    this.config.required           = this.form.get('required');
    this.config.tags               = this.form.get('tags');
    this.config.format             = this.form.get('format');
    this.config.datetime_min       = dateMin;
    this.config.datetime_max       = dateMax;

    this.form.bind('label', this.config.label[this.locale]);

    return true;
};

DatetimeObject.prototype.getTimestampByInternationalFormat = function (date)
{
    if (!date || -1 === date.indexOf('/')) {
        return;
    }

    var splitDate = date.split('/');
    var splitYear = splitDate[2].split(' ');
    var splitTime = splitYear[1].split(':');

    var day = splitDate[0];
    var month = splitDate[1];
    var year = splitYear[0];
    var hours = splitTime[0];
    var minutes = splitTime[1];

    return new Date(year, month, day, hours, minutes).getTime();
};

module.exports = DatetimeObject;
