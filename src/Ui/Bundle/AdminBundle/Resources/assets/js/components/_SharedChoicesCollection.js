import $ from 'jquery';
import SharedChoices from "./_SharedChoices";

function SharedChoicesCollection(element, name)
{
    this.element    = $(element);
    this.name       = name;

    const elements = this.element.find('[data-shared-choices-collection-item="' + this.name + '"]');

    if (elements.length > 10) {
        return;
    }

    elements.each(function (key, item) {
        $(item).data('shared-choices-collection-item-object', new SharedChoices(item, '[data-shared-choices-collection-item="' + item.getAttribute('data-shared-choices-collection-item') + '"]'));
    }.bind(this));

    this.element.on('collection:added', this.added.bind(this));
    this.element.on('collection:deleted', this.refresh.bind(this));
}

SharedChoicesCollection.prototype.added = function (event, item)
{
    item.element.find('[data-shared-choices-collection-item="' + this.name + '"]').each(function (key, element) {
        $(element).data('shared-choices-collection-item-object', new SharedChoices(element, '[data-shared-choices-collection-item="' + element.getAttribute('data-shared-choices-collection-item') + '"]'));
    });
};

SharedChoicesCollection.prototype.refresh = function ()
{
    var object = this.element.find('[data-shared-choices-collection-item="' + this.name + '"]').data('shared-choices-collection-item-object');

    if (object !== undefined) {
        object.refresh();
    }
};

export default SharedChoicesCollection;
