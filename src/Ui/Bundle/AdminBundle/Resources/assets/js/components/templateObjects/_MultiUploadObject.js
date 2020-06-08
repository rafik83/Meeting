import Form from './../template/_Form';

function MultiUploadObject(element, locale, builderType)
{
    this.element = element;
    this.locale = locale;
    this.form = new Form(element);
    this.config = JSON.parse(this.element.getAttribute('data-config'));
    this.templateTaggableObject = null;
    this.builderType = builderType;

    this.uploadFormatRequiredMessage = this.element.getAttribute('data-upload-format-required-message');
}

MultiUploadObject.prototype.fill = function ()
{
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('help', this.config.help[this.locale]);
    this.form.set('required', this.config.required);
    this.form.set('formats', this.config.formats);
    this.form.set('max', this.config.max);
    this.form.set('default', this.config.default);
    this.form.set('titlePlaceholder', this.config.titlePlaceholder[this.locale]);

    this.form.bind('label', this.config.label[this.locale]);
};

MultiUploadObject.prototype.save = function ()
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
    this.config.formats = this.form.get('formats');
    this.config.max = this.form.get('max');
    this.config.default = this.form.get('default');
    this.config.titlePlaceholder[this.locale]  = this.form.get('titlePlaceholder');

    this.form.bind('label', this.config.label[this.locale]);

    return true;
};

export default MultiUploadObject;
