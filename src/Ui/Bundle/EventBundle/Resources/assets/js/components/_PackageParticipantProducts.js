var PackageParticipantRow = require('./_PackageParticipantRow');

function PackageParticipantProducts(element)
{
  this.element = element;
  this.participantProducts = JSON.parse(this.element.getAttribute('data-serialized-participant-products'));

  [].forEach.call(element.querySelectorAll('tr'), function (row) {
    new PackageParticipantRow(this, row);
  }.bind(this));
}

PackageParticipantProducts.prototype.select = function () {
  [].forEach.call(this.element.querySelectorAll('select.participant-product'), function (selectElement) {
    $(selectElement).select2({
      templateResult: this.formatState,
      minimumResultsForSearch: 10
    });
  }.bind(this));
};

PackageParticipantProducts.prototype.getParticipantProduct = function (id) {
  for (var index in this.participantProducts) {
    var participantProduct = this.participantProducts[index];

    if (+id === +participantProduct.id) {
      return participantProduct;
    }
  }

  return null;
};

PackageParticipantProducts.prototype.getParticipantProductPrice = function (id) {
  var participantProduct = this.getParticipantProduct(id);

  if (null === participantProduct) {
    return '';
  }

  return participantProduct.unitPriceFormatted;
};

module.exports = PackageParticipantProducts;
