
var $             = require('jquery'),
    SharedChoices = require('./_SharedChoices');

function SharedChoicesCollection(element, name)
{
    this.element    = $(element);
    this.name       = name;

    this.element.find('[data-shared-choices="' + this.name + '"]').each(function (key, element) {
        $(element).data('shared-choices-object', new SharedChoices(element, '[data-shared-choices="' + element.getAttribute('data-shared-choices') + '"]'));
    }.bind(this));

    this.element.on('collection:added', this.added.bind(this));
    this.element.on('collection:deleted', this.refresh.bind(this));
}

SharedChoicesCollection.prototype.added = function (event, item)
{
    item.element.find('[data-shared-choices="' + this.name + '"]').each(function (key, element) {
        $(element).data('shared-choices-object', new SharedChoices(element, '[data-shared-choices="' + element.getAttribute('data-shared-choices') + '"]'));
    });
};

SharedChoicesCollection.prototype.refresh = function ()
{
    this.element.find('[data-shared-choices="' + this.name + '"]').data('shared-choices-object').refresh();
};

module.exports = SharedChoicesCollection;
