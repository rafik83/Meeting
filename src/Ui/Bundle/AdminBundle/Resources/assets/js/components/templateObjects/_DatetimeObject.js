import Form from './../template/_Form';
import TemplateTaggableObject from './../template/_TemplateTaggableObject';
import DateTimeManipulation from './../_DateTimeManipulation';

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
    this.dateMinShouldBeLessThanDateMaxMessage = this.element.getAttribute('data-date-min-should-be-less-than-date-max-message');

    this.templateTaggableObject = null;

    this.dateTimeManipulation = new DateTimeManipulation();

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
    this.form.set('visibility', this.config.visibility);

    this.form.bind('label', this.config.label[this.locale]);
};

DatetimeObject.prototype.save = function ()
{
    this.config.format = this.form.get('format');

    var dateMin = this.form.get('datetime_min');
    var dateMax = this.form.get('datetime_max');
    var minTime = this.dateTimeManipulation.getTimestampByInternationalFormat(dateMin);
    var maxTime = this.dateTimeManipulation.getTimestampByInternationalFormat(dateMax);

    if ('date' === this.config.format) {
        if (minTime) {
            minTime.setHours(0);
            minTime.setMinutes(0);
            minTime.setSeconds(0);
        }

        if (maxTime) {
            maxTime.setHours(23);
            maxTime.setMinutes(59);
            maxTime.setSeconds(59);
        }
    }

    if ((dateMin && dateMax) && minTime.getTime() > maxTime.getTime()) {
        alert(this.dateMinShouldBeLessThanDateMaxMessage);

        return false;
    }

    if (this.templateTaggableObject && !this.templateTaggableObject.save()) {
        return false;
    }

    this.config.label[this.locale] = this.form.get('label');
    this.config.help[this.locale]  = this.form.get('help');
    this.config.required           = this.form.get('required');
    this.config.tags               = this.form.get('tags');
    this.config.datetime_min       = this.dateTimeManipulation.formatDate(minTime);
    this.config.datetime_max       = this.dateTimeManipulation.formatDate(maxTime);
    this.config.visibility         = this.form.get('visibility');

    this.form.bind('label', this.config.label[this.locale]);

    return true;
};

export default DatetimeObject;
