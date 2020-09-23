import $ from 'jquery';

/**
 * @param {Node} element
 * @constructor
 */
function IgnorePhoneConfirmation(element) {
  this.element    = element;
  this.url        = this.element.getAttribute('data-ignore-phone-confirmation-url');
  this.element.addEventListener('click', this.ignore.bind(this));
}

/**
 * Send ajax call to add ignore phone confirmation
 */
IgnorePhoneConfirmation.prototype.ignore = function() {
  $.ajax({
    url: this.url
  });
};

export default IgnorePhoneConfirmation;
