import $ from 'jquery';
import 'elao-form.js';

function SelectParent(element)
{
    this.element = element;
    this.id      = element.getAttribute('data-parent');
    this.choice  = $(element).choice().data('choice');
    this.parent  = $('#' + this.id).choice().data('choice');

    this.choice.addMatcher(this.id, function (filter, option) {
        return option.data.parent === filter;
    });

    this.parent.element.on('change', this.refresh.bind(this));
    this.refresh();
}

SelectParent.prototype.refresh = function ()
{
    if (this.parent.value === null) {
        this.choice.reset();
    } else {
        this.choice.filter(this.parent.value, this.id);
    }
};

export default SelectParent;
