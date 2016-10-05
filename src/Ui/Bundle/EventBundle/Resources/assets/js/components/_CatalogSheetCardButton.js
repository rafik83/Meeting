var $ = require('jquery');

function CatalogSheetCardButton(element)
{
  this.element = element;
  this.link    = element.getAttribute('data-modal-load');
}

module.exports = CatalogSheetCardButton;
