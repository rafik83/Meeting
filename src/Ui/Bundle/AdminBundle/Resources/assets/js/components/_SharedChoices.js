
var $ = require('jquery');

require('elao-form.js');

function SharedChoices(element, selector)
{
    this.element  = $(element);
    this.selector = selector;
    this.choice   = this.element.choice().data('choice');

    this.choice.addMatcher('my_filter', function (filter, option) {
        console.log(filter);
        return option.isSelected() || filter.indexOf(option.value) === -1;
    });

    this.element.on('change', this.refresh.bind(this));
    this.refresh();
}

SharedChoices.prototype.refresh = function ()
{
    var siblings = $(this.selector);
    var values = [];

    console.log(siblings);

    // Gather selected values & reset all selects
    siblings.each(function (key, element) {
        var choice = $(element).data('choice');
        values.push(choice.value);
        choice.reset();
    });

    // Filter
    siblings.each(function (key, element) {
        var choice = $(element).data('choice');
        choice.filter(values, 'my_filter');
    });
};

module.exports = SharedChoices;
