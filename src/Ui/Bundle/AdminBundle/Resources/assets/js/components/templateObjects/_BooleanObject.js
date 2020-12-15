import Form from './../template/_Form';
import TemplateTaggableObject from './../template/_TemplateTaggableObject';

/**
 * BooleanObject
 *
 * @param element
 * @param locale
 * @param builderType
 *
 * @constructor
 */
function BooleanObject(element, locale, builderType)
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

    this.filterActive = this.element.querySelector('input[name="filter[active]"');
    this.filterLabel = this.element.querySelector('[data-boolean-filter-label]');
    this.filterActive.onchange = this.handleFilterActiveChanged.bind(this);
}

BooleanObject.prototype.fill = function ()
{
    this.form.set('label', this.config.label[this.locale]);
    this.form.set('required', this.config.required);
    this.form.set('tags', this.config.tags);
    this.form.set('filter[active]', this.config.filter.active);
    this.form.set('filter[label]', this.config.filter.label);
    this.form.set('visibility', this.config.visibility);
    this.toggleDisplayLabelFilter(this.config.filter.active);

    this.form.bind('label', this.config.label[this.locale]);
};

BooleanObject.prototype.save = function ()
{
    if (this.templateTaggableObject && !this.templateTaggableObject.save()) {
        return false;
    }

    this.config.label[this.locale] = this.form.get('label');
    this.config.required           = this.form.get('required');
    this.config.tags               = this.form.get('tags');
    this.config.filter.label       = this.form.get('filter[label]');
    this.config.filter.active      = this.form.get('filter[active]');
    this.config.visibility         = this.form.get('visibility');

    this.form.bind('label', this.config.label[this.locale]);

    return true;
};

BooleanObject.prototype.handleFilterActiveChanged = function (event)
{
    this.toggleDisplayLabelFilter(event.target.checked);
};

BooleanObject.prototype.toggleDisplayLabelFilter = function (displayed)
{
    this.filterLabel.style.display = displayed ? 'block' : 'none';
};

export default BooleanObject;
