import $ from 'jquery';
import * as Sortable from "./_Sortable";

function SortableCollection(element, group)
{
    this.element    = $(element);
    this.group      = group;
    this.sortable   = new Sortable(element, { handle: '.sort-handle', onSort: this.update.bind(this) });

    this.element.on('collection:added', this.update.bind(this));
    this.element.on('collection:deleted', this.update.bind(this));
}

SortableCollection.prototype.update = function ()
{
    this.element.find('[data-rank="' + this.group + '"]').each (function (key, element) {
        element.value = key;
    });
};

export default SortableCollection;
