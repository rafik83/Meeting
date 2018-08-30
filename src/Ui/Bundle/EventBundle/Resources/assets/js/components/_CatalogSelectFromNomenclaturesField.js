var $ = require('jquery'),
    CheckAllButton = require('./_CheckAllButton');

function CatalogSelectFromNomenclaturesField(element, modal) {
    this.element = element;
    this.modal = modal;
    this.cachedForms = [];

    this.initialSelect = this.element.querySelector('select');

    this.link = this.element.querySelector('a');
    this.link.addEventListener('click', this.handleClick.bind(this));
}

CatalogSelectFromNomenclaturesField.prototype.showPlaceholder = function () {
    var placeholder = this.modal.getAttribute('data-placeholder');
    this.showModalContent(placeholder);
};

CatalogSelectFromNomenclaturesField.prototype.handleClick = function (event) {
    event.preventDefault();
    var href = this.link.getAttribute('href');

    this.showPlaceholder();

    if (this.cachedForms[href]) {
        this.showModalContent(this.cachedForms[href]);
    } else {
        this.loadForm(href);
    }
};

CatalogSelectFromNomenclaturesField.prototype.loadForm = function (href) {
    $.get(href, function (response) {
        this.cachedForms[href] = response;
        this.showModalContent(response);
    }.bind(this))
        .fail(function () {
            alert('Error');
        }.bind(this));
};

CatalogSelectFromNomenclaturesField.prototype.showModalContent = function (html) {
    $(this.modal).find('.modal-content').html(html);
    $(this.modal).modal('show');

    var modalTitle = $(this.modal).find('.modal-title');

    if (modalTitle) {
        modalTitle.html(this.element.querySelector('[data-title]').textContent)
    }

    this.initModalContent();
};

CatalogSelectFromNomenclaturesField.prototype.initModalContent = function () {
    [].forEach.call(this.modal.querySelectorAll('[data-check-all-button]'), function (element) {
        new CheckAllButton(element, element.getAttribute('data-check-all-button'), true)
    });

    [].forEach.call(this.modal.querySelectorAll('[data-uncheck-all-button]'), function (element) {
        new CheckAllButton(element, element.getAttribute('data-uncheck-all-button'), false)
    });

    this.form = this.modal.querySelector('form');

    if (this.form) {
        this.form.addEventListener('submit', this.handleForm.bind(this));
    }
};

CatalogSelectFromNomenclaturesField.prototype.handleForm = function (event) {
    event.preventDefault();
    $(this.initialSelect).val(this.getCheckedValues());
    $(this.modal).modal('hide');

    var htmlEvent = document.createEvent('HTMLEvents');
    htmlEvent.initEvent('change', true, true);
    this.initialSelect.dispatchEvent(htmlEvent);
};

CatalogSelectFromNomenclaturesField.prototype.getCheckedValues = function () {
    var checked = this.form.querySelectorAll('input[type="checkbox"]:checked');
    return Array.prototype.map.call(checked, function (element) {
        return element.value;
    });
};

module.exports = CatalogSelectFromNomenclaturesField;
