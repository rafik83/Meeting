var $ = require('jquery');

/**
 * SelectPackageProduct component
 *
 * @constructor
 */
function SelectPackageProduct() {

  $('[data-package-product-included-select]').each(function () {
    $(this).choice();
  });


  this.selectTheProducts();

  $('[data-collection-product-included]').on('collection:added', $('[data-collection-product-included]'), function (event, item) {
    var select = item.element.find('[data-package-product-included-select]');

    select.on('change', function () {
      this.selectTheProducts();
    }.bind(this)).choice();

    this.selectTheProducts();
  }.bind(this));

  $('[data-collection-product-included]').on('collection:deleted', $('[data-collection-product-included]'), function (e) {
    this.selectTheProducts();
  }.bind(this));
}

SelectPackageProduct.prototype.selectTheProducts = function ()
{
  var selectedValue = [];

  // Gather selected values
  // Reset all selects with their previous datas
  $('[data-package-product-included-select]').each(function () {
    selectedValue.push($(this).val());
    $(this).data('choice').reset();
  });

  // Remove selected data on other selects
  $('[data-package-product-included-select]').each(function () {
    for(var i = 0; i < selectedValue.length; i++) {
      if (selectedValue[i] !== $(this).val()) {

        // find the option with the val = selectedValue[i]
        var options = this.options;
        for (var j = 0; j < options.length; j++) {
          if (options[j].value === selectedValue[i]) {
            this.remove(j);
          }
        }
      }
    }
  });
};

module.exports = SelectPackageProduct;
