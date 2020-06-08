import Form from './../template/_Form';
import TemplateTaggableObject from './../template/_TemplateTaggableObject';

/**
 * UploadObject
 *
 * @param element
 * @param locale
 * @param builderType
 *
 * @constructor
 */
function UploadObject(element, locale, builderType)
{
    this.element = element;
    this.locale = locale;
    this.form = new Form(element);
    this.config = JSON.parse(this.element.getAttribute('data-config'));
    this.templateTaggableObject = null;
    this.builderType = builderType;

    if (element.querySelector('[data-template-tags-select]')) {
        this.templateTaggableObject = new TemplateTaggableObject(element);
    }

    this.uploadFormatRequiredMessage = this.element.getAttribute('data-upload-format-required-message');
    this.filterActive = this.element.querySelector('input[name="filter[active]"');
    this.filterLabel = this.element.querySelector('[data-upload-filter-label]');
    this.filterActive.onchange = this.handleFilterActiveChanged.bind(this);
}

UploadObject.prototype.fill = function ()
{
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('help', this.config.help[this.locale]);
    this.form.set('required', this.config.required);
    this.form.set('crypted', this.config.crypted);
    this.form.set('formats', this.config.formats);
    this.form.set('tags', this.config.tags);
    this.form.set('filter[active]', this.config.filter.active);
    this.form.set('filter[label]', this.config.filter.label);
    this.form.set('visibility', this.config.visibility);
    this.toggleDisplayLabelFilter(this.config.filter.active);

    this.form.bind('label', this.config.label[this.locale]);
};

UploadObject.prototype.save = function ()
{
    if (0 === this.form.get('formats').length) {
        alert(this.uploadFormatRequiredMessage);

        return false;
    }

    if (this.templateTaggableObject && !this.templateTaggableObject.save()) {
        return false;
    }

    this.config.label[this.locale] = this.form.get('label');
    this.config.help[this.locale] = this.form.get('help');
    this.config.required  = this.form.get('required');
    this.config.crypted = this.form.get('crypted');
    this.config.tags = this.form.get('tags');
    this.config.formats = this.form.get('formats');
    this.config.filter.label = this.form.get('filter[label]');
    this.config.filter.active = this.form.get('filter[active]');
    this.config.visibility = this.form.get('visibility');

    this.form.bind('label', this.config.label[this.locale]);

    return true;
};

UploadObject.prototype.handleFilterActiveChanged = function (event)
{
    this.toggleDisplayLabelFilter(event.target.checked);
};

UploadObject.prototype.toggleDisplayLabelFilter = function (displayed)
{
    this.filterLabel.style.display = displayed ? 'block' : 'none';
};

export default UploadObject;
