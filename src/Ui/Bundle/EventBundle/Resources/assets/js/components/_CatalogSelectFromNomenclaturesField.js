var $ = require('jquery'),
  CheckAllButton = require('./_CheckAllButton');

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

  var modalTitle = $(this.modal).find('.modal-title');
  if (modalTitle) {
    modalTitle.html(this.element.querySelector('[data-title]').textContent)
  }

  [].forEach.call(this.modal.querySelectorAll('[data-check-all-button]'), function (element) {
    new CheckAllButton(element, element.getAttribute('data-check-all-button'), true)
  });

  [].forEach.call(this.modal.querySelectorAll('[data-uncheck-all-button]'), function (element) {
    new CheckAllButton(element, element.getAttribute('data-uncheck-all-button'), false)
  });
};

module.exports = CatalogSelectFromNomenclaturesField;
