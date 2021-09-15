import $ from 'jquery';
import 'select2';

function CommercialStatusSelect(element)
{
    this.element = element;
    this.associatedLabel = JSON.parse(this.element.getAttribute('data-associated-label'));

    $(this.element).select2({
        width: '100%',
        templateResult: this.formatResult.bind(this),
        templateSelection: this.formatResult.bind(this)
    }).bind(this);
}

CommercialStatusSelect.prototype.formatResult = function(status)
{
    var label = 'default';

    if (status.id && this.associatedLabel.hasOwnProperty(status.element.value)) {
        label = this.associatedLabel[status.element.value];
    }

    return $("<span class='label label-" + label + "'>" + status.text + "</span>");
};


export default CommercialStatusSelect;
