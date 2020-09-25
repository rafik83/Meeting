import $ from 'jquery';

function CatalogSheetCardButton(element)
{
  this.element = element;
  this.link    = element.getAttribute('data-modal-load');
}

export default CatalogSheetCardButton;
