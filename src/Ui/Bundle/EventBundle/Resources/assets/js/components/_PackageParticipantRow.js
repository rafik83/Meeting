var $ = require('jquery');
require('select2');

function PackageParticipantRow(parent, element)
{
  this.parent = parent;
  this.element = element;

  this.select();
}

PackageParticipantRow.prototype.select = function () {
  [].forEach.call(this.element.querySelectorAll('select.participant-product'), function (selectElement) {
    $(selectElement).select2({
      templateResult: this.formatState.bind(this),
      minimumResultsForSearch: 10
    });
  }.bind(this));
};

PackageParticipantRow.prototype.formatState = function (state)
{
  if (!state.id) {
    return state.text;
  }

  var participantProductPrice = this.parent.getParticipantProductPrice(state.id);

  return $('<div class="row"><div class="col-md-8">' + state.text + '</div><div class="col-md-4 text-right">' + participantProductPrice + '</div></div>');
};

module.exports = PackageParticipantRow;
