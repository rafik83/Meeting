var PackageParticipantRow = require('./_PackageParticipantRow');

function PackageParticipantProducts(element)
{
  this.element = element;
  var participantProductsObjects = JSON.parse(this.element.getAttribute('data-serialized-participant-products'));

  this.participantProducts = [];

  for (var index in participantProductsObjects) {
    this.participantProducts[+participantProductsObjects[index].id] = new PackageParticipantProductModel(this, participantProductsObjects[index]);
  }

  this.packageParticipantRows = [];

  [].forEach.call(element.querySelectorAll('tr'), function (row) {
    this.packageParticipantRows.push(new PackageParticipantRow(this, row));
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
  if (this.participantProducts[+id]) {
    return this.participantProducts[+id]
  }

  return null;
};

PackageParticipantProducts.prototype.hasRemainingQuantity = function (packageParticipantRowToExclude, productId) {
  var participantProduct = this.getParticipantProduct(productId);

  if (participantProduct.isInfiniteQuantityMax()) {
    return true;
  }

  var selectedQuantity = 0;

  for (var index in this.packageParticipantRows) {
    var packageParticipantRow = this.packageParticipantRows[index];

    if (packageParticipantRowToExclude === packageParticipantRow) {
      continue;
    }

    if (packageParticipantRow.getValue() === participantProduct.id) {
      selectedQuantity++;
    }
  }

  return selectedQuantity < participantProduct.quantityMax;
};

module.exports = PackageParticipantProducts;

function PackageParticipantProductModel(parent, participantProduct)
{
  this.parent = parent;

  this.id = +participantProduct.id;
  this.title = participantProduct.title;
  this.description = participantProduct.description;
  this.unitPrice = participantProduct.unitPrice;
  this.currency = participantProduct.currency;
  this.vatMode = participantProduct.vatMode;
  this.unitPriceFormatted = participantProduct.unitPriceFormatted;
  this.quantityIncluded = +participantProduct.quantityIncluded;
  this.quantityMax = 'Infinity' === participantProduct.quantityMax ? Infinity : participantProduct.quantityMax;
  this.quantityMaxFormatted = participantProduct.quantityMaxFormatted;
}

PackageParticipantProductModel.prototype.isInfiniteQuantityMax = function ()
{
  return Infinity === this.quantityMax;
};
