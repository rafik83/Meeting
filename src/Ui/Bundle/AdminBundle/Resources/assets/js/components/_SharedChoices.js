import $ from 'jquery';
import 'elao-form.js';

function SharedChoices(element, selector)
{
    this.element  = $(element);
    this.selector = selector;
    this.choice   = this.element.choice().data('choice');

    this.choice.addMatcher('my_filter', function (filter, option) {
        return option.isSelected() || filter.indexOf(option.value) === -1;
    });

    this.element.on('change', this.refresh.bind(this));
    //this.element.on('focus', this.refresh.bind(this));
    this.refresh();
}

SharedChoices.prototype.refresh = function ()
{
    var siblings = $(this.selector);
    var values = [];

    // Gather selected values & reset all selects
    siblings.each(function (key, element) {
        var choice = $(element).data('choice');

        if (choice !== undefined && typeof choice === 'object') {
            values.push(choice.value);
            choice.reset();
        }
    });

    // Filter
    siblings.each(function (key, element) {
        var choice = $(element).data('choice');

        if (choice !== undefined && typeof choice === 'object') {
            choice.filter(values, 'my_filter');
        }
    });
};

export default SharedChoices;
