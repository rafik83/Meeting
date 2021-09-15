import $ from 'jquery';

/**
 * @param form the form surrounding the select
 * @param element the select element
 *
 * @constructor
 */
function SelectPreviousNextMover(form, element) {
    this.form = form;
    this.element = element;
    this.nextButtons = [];
    this.previousButtons = [];

    [].forEach.call(this.form.querySelectorAll('[data-select-mover-next]'), function (nextButton) {
        this.nextButtons.push(nextButton);
    }.bind(this));

    [].forEach.call(this.form.querySelectorAll('[data-select-mover-previous]'), function (previousButton) {
        this.previousButtons.push(previousButton);
    }.bind(this));

    this.nextButtons.forEach(function(nextButton) {
        nextButton.addEventListener('click', function(event) {
            event.preventDefault();

            this.selectNextOrPrevious('next');
        }.bind(this))
    }.bind(this));

    this.previousButtons.forEach(function(previousButton) {
        previousButton.addEventListener('click', function(event) {
            event.preventDefault();

            this.selectNextOrPrevious('previous');
        }.bind(this));
    }.bind(this));
}

SelectPreviousNextMover.prototype.selectNextOrPrevious = function (nextOrPrevious) {
    var nextIndex = 1;

    if (nextOrPrevious === 'previous') {
        nextIndex = -1;
    }

    var selectedElement = $('option:selected', this.element);
    var index = $('option', this.element).index(selectedElement);
    var nextElement = $('option', this.element).eq(index + nextIndex);

    if (nextElement.length > 0) {
        $(selectedElement).removeAttr('selected');
        $(nextElement).attr('selected', 'selected');

        this.dispatchChangeEvent();
    }
};

SelectPreviousNextMover.prototype.dispatchChangeEvent = function()
{
    var htmlEvent = document.createEvent('HTMLEvents');
    htmlEvent.initEvent('change', true, true);

    this.element.dispatchEvent(htmlEvent);
};


export default SelectPreviousNextMover;
