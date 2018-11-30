var $ = require('jquery');

function ShowModal(element) {
    this.element = element;

    $(this.element).modal('show');
}

module.exports = ShowModal;
