
var $        = require('jquery'),
    Sortable = require('./_Sortable');

function SortableCollection(element)
{
    this.element    = $(element);
    this.sortable   = new Sortable(element, { handle: '.sort-handle', onSort: this.update.bind(this) });

    this.element.on('collection:added', this.update.bind(this));
    this.element.on('collection:deleted', this.update.bind(this));
}

SortableCollection.prototype.update = function ()
{
    this.element.find('[data-rank]').each (function (key, element) {
        element.value = key;
    });
};

module.exports = SortableCollection;
