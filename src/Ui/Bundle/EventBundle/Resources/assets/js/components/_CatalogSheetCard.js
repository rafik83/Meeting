import $ from 'jquery';
import CatalogSheetCardButton from "./_CatalogSheetCardButton";
import CatalogSheetCardRequestCheckbox from "./_CatalogSheetCardRequestCheckbox";
import EditableTextIndicator from "./_EditableTextIndicator";

function CatalogSheetCard(element, modal) {
    this.element = element;
    this.buttonsZones = element.querySelectorAll('.buttons-zone');
    this.buttons = [];
    this.modal = modal;

    this.identifyButtons();
}

CatalogSheetCard.prototype.identifyButtons = function () {
    this.buttons = [];

    [].forEach.call(this.element.querySelectorAll('.buttons-zone .btn'), function (element) {
        var button = new CatalogSheetCardButton(element);
        this.buttons.push(button);

        button.element.addEventListener('click', function () {
            this.onButtonClick(button)
        }.bind(this, button), false);
    }.bind(this));
};

CatalogSheetCard.prototype.onButtonClick = function (button) {
    if (button.link !== null) {
        // Add the possible placeholder (eg: spinning arrow)
        var placeholder = this.modal.getAttribute('data-placeholder');
        $(this.modal).find(".modal-content").html(placeholder);

        // Load content from ajax and display the modal
        $(this.modal).find(".modal-content").load(button.link, function (response, status) {
            if (status === 'error') {
                this.displayErrorLoading();
            } else if (status === 'success') {
                this.putListenerOnRequestForm();
            }
        }.bind(this));
        $(this.modal).modal();
    }
};

CatalogSheetCard.prototype.displayErrorLoading = function () {
    var errorMessage = this.modal.getAttribute('data-loading-error');
    this.replaceModalContent(errorMessage);
};

CatalogSheetCard.prototype.updateParticipantsHtml = function (participantsHtml) {
    if ('' !== participantsHtml) {
        $(this.element).find('.participants-list').html(participantsHtml);
    }
};

CatalogSheetCard.prototype.putListenerOnRequestForm = function () {
    if (this.modal.querySelector('[data-participants-checkbox]') !== null) {
        new CatalogSheetCardRequestCheckbox(this.modal.querySelector('[data-participants-checkbox]'));
    }

    var discussionElement = this.modal.querySelector('[data-text-max-length-indicator]');
    if (discussionElement !== null) {
        new EditableTextIndicator(
            discussionElement,
            discussionElement.getAttribute('data-text-max-length-indicator'),
            discussionElement.getAttribute('data-text-max-length-translations')
        );
    }

    [].forEach.call(this.modal.querySelectorAll('form'), function (form) {
        $(form).on('submit', function (event) {
            this.handleRequestForm(form);

            return false;
        }.bind(this));
    }.bind(this));
};

/**
 * @param {String} html
 */
CatalogSheetCard.prototype.replaceButtonsZone = function(html) {
    [].forEach.call(this.buttonsZones, function (buttonZone) {
        $(buttonZone).html(html)
    });
};

/**
 * @param {String} html
 */
CatalogSheetCard.prototype.replaceModalContent = function(html) {
    $(this.modal).find(".modal-content").html(html);
};

CatalogSheetCard.prototype.handleRequestForm = function (form) {
    var action = $(form).attr('action');
    var data = $(form).serialize();

    // Put the placeholder during the ajax call to avoid mistake of the user
    var placeholder = this.modal.getAttribute('data-placeholder');
    this.replaceModalContent(placeholder);

    // Update sheets list
    $.post(action, data, function (response) {
        if (response.close === true) {
            if (response.status === 'ok') {
                this.replaceButtonsZone(response.html);
                $(this.modal).modal('hide');
                this.identifyButtons();
                this.updateParticipantsHtml(response.participantsHtml);
            }
        } else {
            if (response.status === 'ok') {
                if (response.flashMessage !== undefined) {
                    this.replaceModalContent(response.flashMessage);
                    this.replaceButtonsZone(response.html);
                } else {
                    this.replaceModalContent(response.html);
                    this.updateParticipantsHtml(response.participantsHtml);
                }
            }
        }

        if (response.status === 'error') {
            this.replaceModalContent(response.html);
            this.putListenerOnRequestForm();
        }
    }.bind(this)).fail(function () {
        this.displayErrorLoading();
    }.bind(this));

    return false;
};

export default CatalogSheetCard;
