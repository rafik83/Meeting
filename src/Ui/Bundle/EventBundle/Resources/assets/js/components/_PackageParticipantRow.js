var $ = require('jquery');
require('select2');

function PackageParticipantRow(parent, element)
{
  this.parent = parent;
  this.element = element;
  this.selectElement = this.element.querySelector('select.participant-product');
  this.productTitleTextElement = this.element.querySelector('[data-product-title-text]');
  this.productTitleElement = this.element.querySelector('[data-product-title]');
  this.productDescriptionElement = this.element.querySelector('[data-product-description]');
  this.productIncludedElement = this.element.querySelector('[data-product-included]');
  this.productPriceElement = this.element.querySelector('[data-product-price]');

  this.productTitleElement.querySelector('a').addEventListener('click', this.editRow.bind(this));

  if (this.selectElement.value) {
    this.hide(this.selectElement);
    this.updateRow(this.selectElement.value);
  } else {
    this.initSelect();
  }

  $(this.selectElement).on('select2:close', function (event) {
    if (event.target.value) {
      $(this.selectElement).select2('destroy');
      this.updateRow(event.target.value);
    }
  }.bind(this));
}

PackageParticipantRow.prototype.show = function (element) {
  element.classList.remove('hidden');
};

PackageParticipantRow.prototype.hide = function (element) {
  element.classList.add('hidden');
};

PackageParticipantRow.prototype.initSelect = function () {
  this.hide(this.productTitleElement);
  this.hide(this.productDescriptionElement);
  this.hide(this.productIncludedElement);
  this.hide(this.productPriceElement);

  this.show(this.selectElement);

  $(this.selectElement).select2({
    templateResult: this.formatState.bind(this),
    minimumResultsForSearch: 10
  });

  this.hide(this.selectElement);
};

PackageParticipantRow.prototype.editRow = function (event) {
  event.preventDefault();
  this.initSelect();
  $(this.selectElement).select2('open');
};

PackageParticipantRow.prototype.updateRow = function (productId)
{
  var participantProduct = this.parent.getParticipantProduct(productId);

  if (!participantProduct) {
    return;
  }

  var price = this.parent.getParticipantProductPrice(productId);

  this.productTitleTextElement.innerHTML = participantProduct.title;
  this.productDescriptionElement.innerHTML = participantProduct.description;
  this.productPriceElement.innerHTML = price;
  this.show(this.productTitleElement);
  this.show(this.productDescriptionElement);
  this.show(this.productPriceElement);
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
