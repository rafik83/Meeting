import Form from './../template/_Form';
import TemplateTaggableObject from './../template/_TemplateTaggableObject';

/**
 * GenderObject
 *
 * @param element
 * @param locale
 * @param builderType
 *
 * @constructor
 */
function GenderObject(element, locale, builderType)
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
}

GenderObject.prototype.fill = function ()
{
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('required', this.config.required);
    this.form.set('tags', this.config.tags);
    this.form.set('visibility', this.config.visibility);

    this.form.bind('label', this.config.label[this.locale]);
};

GenderObject.prototype.save = function ()
{
    if (this.templateTaggableObject && !this.templateTaggableObject.save()) {
        return false;
    }

    this.config.label[this.locale] = this.form.get('label');
    this.config.required           = this.form.get('required');
    this.config.tags               = this.form.get('tags');
    this.config.visibility         = this.form.get('visibility');

    this.form.bind('label', this.config.label[this.locale]);

    return true;
};

export default GenderObject;
