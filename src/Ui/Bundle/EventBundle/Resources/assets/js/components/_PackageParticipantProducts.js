import PackageParticipantRow from "./_PackageParticipantRow";

function PackageParticipantProducts(element)
{
  this.element = element;
  this.participantsIncludedLabel = this.element.getAttribute('data-participants-included-label');

  this.participantProducts = [];
  var participantProductsObjects = JSON.parse(this.element.getAttribute('data-serialized-participant-products'));

  for (var index in participantProductsObjects) {
    this.participantProducts[+participantProductsObjects[index].id] = new PackageParticipantProductModel(this, participantProductsObjects[index]);
  }

  this.packageParticipantRows = [];

  [].forEach.call(element.querySelectorAll('tr'), function (row) {
    this.packageParticipantRows.push(new PackageParticipantRow(this, row));
  }.bind(this));

  this.updateRows();
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

PackageParticipantProducts.prototype.getUnitPriceFormattedOrIncluded = function (productId, packageParticipantRowToExclude) {
  var participantProduct = this.getParticipantProduct(productId);

  if (!participantProduct.quantityIncluded) {
    return participantProduct.unitPriceFormatted;
  }

  var quantitySelected = this.getSelectedQuantityByProduct(productId, packageParticipantRowToExclude);

  return  quantitySelected < participantProduct.quantityIncluded
    ? this.participantsIncludedLabel
    : participantProduct.unitPriceFormatted;
};

PackageParticipantProducts.prototype.hasRemainingQuantity = function (productId, packageParticipantRowToExclude) {
  var participantProduct = this.getParticipantProduct(productId);

  if (participantProduct.isInfiniteQuantityMax()) {
    return true;
  }

  return this.getSelectedQuantityByProduct(productId, packageParticipantRowToExclude) < participantProduct.quantityMax;
};

PackageParticipantProducts.prototype.getSelectedQuantityByProduct = function (productId, packageParticipantRowToExclude) {
  var participantProduct = this.getParticipantProduct(productId);

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

  return selectedQuantity;
};

PackageParticipantProducts.prototype.updateRows = function () {
  var quantitySelectedByProduct = [];

  for (var index in this.packageParticipantRows) {
    var packageParticipantRow = this.packageParticipantRows[index];
    var productId = packageParticipantRow.getValue();

    if (!productId) {
      packageParticipantRow.refreshSelect();

      continue;
    }

    var participantProduct = this.getParticipantProduct(productId);

    if (!quantitySelectedByProduct[productId]) {
      quantitySelectedByProduct[productId] = 0;
    }

    quantitySelectedByProduct[productId]++;

    var priceOrIncluded = quantitySelectedByProduct[productId] <= participantProduct.quantityIncluded
      ? this.participantsIncludedLabel
      : participantProduct.unitPriceFormatted;

    packageParticipantRow.updatePriceOrIncluded(priceOrIncluded);
  }
};


PackageParticipantProducts.prototype.hasOnlyOneProduct = function ()
{
  var count = 0;

  for (var index in this.participantProducts) {
    count++;
  }

  return 1 === count;
};

export default PackageParticipantProducts;

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
