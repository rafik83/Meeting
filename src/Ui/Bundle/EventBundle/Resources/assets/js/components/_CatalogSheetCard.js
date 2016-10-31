var $                               = require('jquery'),
    CatalogSheetCardButton          = require('./_CatalogSheetCardButton'),
    CatalogSheetCardRequestCheckbox = require('./_CatalogSheetCardRequestCheckbox');

function CatalogSheetCard(element, modal)
{
    this.element      = element;
    this.buttonsZones = element.querySelectorAll('.buttons-zone');
    this.buttons      = [];
    this.modal        = modal;

    this.identifyButtons();
}

CatalogSheetCard.prototype.identifyButtons = function ()
{
    this.buttons = [];

    [].forEach.call(this.element.querySelectorAll('.buttons-zone .btn'), function (element) {
        var button = new CatalogSheetCardButton(element);
        this.buttons.push(button);

        button.element.addEventListener('click', function () {
            this.onButtonClick(button)
        }.bind(this, button), false);
    }.bind(this));
};

CatalogSheetCard.prototype.onButtonClick = function(button)
{
    if (button.link !== null) {
        // Add the possible placeholder (eg: spinning arrow)
        var placeholder = this.modal.getAttribute('data-placeholder');
        $(this.modal).find(".modal-content").html(placeholder);

        // Load content from ajax and display the modal
        $(this.modal).find(".modal-content").load(button.link, function () {
            this.putListenerOnRequestForm();
        }.bind(this));
        $(this.modal).modal();
    }
};

CatalogSheetCard.prototype.putListenerOnRequestForm = function ()
{
    if (this.modal.querySelector('[data-participants-checkbox]') !== null) {
        new CatalogSheetCardRequestCheckbox(this.modal.querySelector('[data-participants-checkbox]'));
    }

    [].forEach.call(this.modal.querySelectorAll('form'), function (form) {
        $(form).on('submit', function (event) {
            this.handleRequestForm(form);

            return false;
        }.bind(this));
    }.bind(this));
};

CatalogSheetCard.prototype.handleRequestForm = function (form)
{
    var action = $(form).attr('action');
    var data   = $(form).serialize();

    // Put the placeholder during the ajax call to avoid mistake of the user
    var placeholder = this.modal.getAttribute('data-placeholder');
    $(this.modal).find(".modal-content").html(placeholder);

    // Update sheets list
    $.post(action, data, function(response) {
        if (response.close === true) {
            if (response.status === 'ok') {
                [].forEach.call(this.buttonsZones, function (buttonZone) {
                    $(buttonZone).html(response.html)
                });
                $(this.modal).modal('hide');
                this.identifyButtons();
            }
        } else {
            if (response.status === 'ok') {
                $(this.modal).find(".modal-content").html(response.html);
            }
        }

        if (response.status === 'error') {
            $(this.modal).find(".modal-content").html(response.html);
            this.putListenerOnRequestForm();
        }
    }.bind(this));

    return false;
};

module.exports = CatalogSheetCard;
