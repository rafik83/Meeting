var $                     = require('jquery'),
    EditableTextIndicator = require('./_EditableTextIndicator');

function Happening(happening, modal)
{
    this.modal = modal;
    this.happening = happening;
    this.happeningParticipateIcon = happening.querySelector('.happeningParticipateIcon');
    this.happeningParticipateAction = happening.querySelector('.happeningParticipateAction');

    if (null !== this.happeningParticipateAction) {
        this.picto = this.happeningParticipateAction.getAttribute('data-picto');
        this.labelParticipate = this.happeningParticipateAction.getAttribute('data-label-participate');
        this.labelUpdate = this.happeningParticipateAction.getAttribute('data-label-participation-update');
        this.labelCancel = this.happeningParticipateAction.getAttribute('data-label-cancel');

        this.happeningParticipateAction.addEventListener('click', function (event) {
            event.stopPropagation();
            event.preventDefault();
            this.onParticipate();
        }.bind(this), false);
    }
}

Happening.prototype.onParticipate = function ()
{
    this.happeningParticipateAction.disabled = true;
    this.happeningParticipateAction.classList.add('disabled');
    this.handleRequestParticipateButton();
};

Happening.prototype.handleRequestParticipateButton = function ()
{
    var href = this.happeningParticipateAction.getAttribute('href');

    $.get(href, function (response) {
        if ('show-form' === response.status) {
            this.enableParticipateAction();
            this.showModal(response.html);
        } else if ('error' === response.status) {
            alert(response.message);
            this.enableParticipateAction();
        } else {
            this.validateParticipation(response);
        }
    }.bind(this))
    .fail(function () {
        this.enableParticipateAction();
    }.bind(this));
};

Happening.prototype.enableParticipateAction = function () {
    this.happeningParticipateAction.disabled = false;
    this.happeningParticipateAction.classList.remove('disabled');
};

Happening.prototype.showModal = function (html) {
    var modal = $(this.modal);
    modal.modal().show();

    var content = modal.find('.modal-content');
    content.html(html);

    var form = content.find('form');

    [].forEach.call(this.modal.querySelectorAll('[data-text-max-length-indicator]'), function (element) {
        new EditableTextIndicator(element, element.getAttribute('data-text-max-length-indicator'), element.getAttribute('data-text-max-length-translations'));
    });

    $(form).on('submit', function () {
        this.handleRequestForm(form);

        return false;
    }.bind(this));
};

Happening.prototype.handleRequestForm = function (form)
{
    var action = $(form).attr('action');
    var data   = $(form).serialize();

    // Put the placeholder during the ajax call to avoid mistake of the user
    var placeholder = this.modal.getAttribute('data-placeholder');
    $(this.modal).find('.modal-content').html(placeholder);

    // Update sheets list
    $.post(action, data, function(response) {
        if ('show-form' === response.status) {
            this.showModal(response.html);
        } else if ('error' === response.status) {
            alert(response.message);
            $(this.modal).modal('hide');
            this.enableParticipateAction();
        } else {
            $(this.modal).modal('hide');
            this.validateParticipation(response);
        }
    }.bind(this));

    return false;
};

Happening.prototype.validateParticipation = function (response) {
    this.enableParticipateAction();
    const label = response.label;

    if (label == undefined) {
        return;
    }

    if ('redirect' === label) {
        document.location.href = response.redirectTo;

        return;
    }

    var buttonLabel;

    if ('cancel' === label) {
        buttonLabel = '<i class="icon icon-Fermer_4 happeningParticipateIcon"></i> ' + this.labelCancel;
        this.happeningParticipateIcon.classList.remove('hide');
    } else if ('update' === label) {
        buttonLabel = '<i class="icon icon-Fermer_4 happeningParticipateIcon"></i> ' + this.labelUpdate;
        this.happeningParticipateIcon.classList.remove('hide');
    } else if ('participate' === label) {
        buttonLabel = '<i class="icon icon-Valider_4 happeningParticipateIcon"></i> ' + this.labelParticipate;
        this.happeningParticipateIcon.classList.add('hide');
    }
    if (buttonLabel) {
        this.happeningParticipateAction.innerHTML = buttonLabel;
    }
};

module.exports = Happening;
