var $ = require('jquery');

function CatalogSelectFromNomenclaturesField(element, modal) {
    this.element = element;
    this.modal = modal;

    this.link = this.element.querySelector('a');
    this.link.addEventListener('click', this.handleClick.bind(this));
}

CatalogSelectFromNomenclaturesField.prototype.showPlaceholder = function ()
{
    var placeholder = this.modal.getAttribute('data-placeholder');
    this.showModalContent(placeholder);
};

CatalogSelectFromNomenclaturesField.prototype.handleClick = function (event)
{
  event.preventDefault();
  var href = this.link.getAttribute('href');

  this.showPlaceholder();

  $.get(href, function (response) {
      this.showModalContent(response);
  }.bind(this))
    .fail(function () {
        alert('Error');
    }.bind(this));
};

CatalogSelectFromNomenclaturesField.prototype.showModalContent = function (html)
{
  $(this.modal).find('.modal-content').html(html);
  $(this.modal).modal().show();
};

module.exports = CatalogSelectFromNomenclaturesField;
