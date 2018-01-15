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
    this.hideSelect();
    this.updateRow(this.selectElement.value);
  } else {
    this.initSelect();
  }

  $(this.selectElement).on('select2:close', function (event) {
    if (event.target.value) {
      $(this.selectElement).select2('destroy');
      this.hideSelect();
      this.updateRow(event.target.value);
    }
  }.bind(this));
}

PackageParticipantRow.prototype.getValue = function () {
  return +this.selectElement.value;
};

PackageParticipantRow.prototype.show = function (element) {
  element.classList.remove('hidden');
};

PackageParticipantRow.prototype.hide = function (element) {
  element.classList.add('hidden');
};

PackageParticipantRow.prototype.showSelect = function () {
  this.selectElement.classList.remove('select2-hidden-accessible');
};

PackageParticipantRow.prototype.hideSelect = function (element) {
  this.selectElement.classList.add('select2-hidden-accessible');
};

PackageParticipantRow.prototype.initSelect = function () {
  this.hide(this.productTitleElement);
  this.hide(this.productDescriptionElement);
  this.hide(this.productIncludedElement);
  this.hide(this.productPriceElement);

  this.showSelect();

  // Prepare select options
  [].forEach.call(this.selectElement.querySelectorAll('option'), this.prepareOption.bind(this));

  $(this.selectElement).select2({
    templateResult: this.formatState.bind(this),
    minimumResultsForSearch: 10
  });
};

PackageParticipantRow.prototype.prepareOption = function (option) {
  var productId = option.value;

  if (!productId) {
    return;
  }

  if (!this.parent.hasRemainingQuantity(this, productId)) {
    option.setAttribute('disabled', 'disabled');
  } else {
    option.removeAttribute('disabled');
  }
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

  this.productTitleTextElement.innerHTML = participantProduct.title;
  this.productDescriptionElement.innerHTML = participantProduct.description;
  this.productPriceElement.innerHTML = participantProduct.unitPriceFormatted;
  this.show(this.productTitleElement);
  this.show(this.productDescriptionElement);
  this.show(this.productPriceElement);
};

PackageParticipantRow.prototype.formatState = function (state)
{
  if (!state.id) {
    return state.text;
  }

  var participantProduct = this.parent.getParticipantProduct(state.id);

  return $('<div class="row"><div class="col-md-8">' + participantProduct.title + (!participantProduct.isInfiniteQuantityMax() ? ' <span class="label label-default">' + participantProduct.quantityMaxFormatted + '</span>' : '') + '</div><div class="col-md-4 text-right">' + participantProduct.unitPriceFormatted + '</div></div>');
};

module.exports = PackageParticipantRow;
