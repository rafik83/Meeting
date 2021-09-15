import EventEmitter from "./_EventEmitter";

/**
 * Meet
 *
 * @param {Agenda} agenda
 * @param {Element} element
 * @param {Element} modal
 */
function Meet(agenda, element, modal) {
    EventEmitter.call(this, element);

    this.modal = modal;
    this.agenda = agenda;
    this.header = this.element.querySelector('header');
    this.details = this.element.querySelector('.details');
    this.updateMeetingAction = this.element.querySelector('.updateMeetingAction');
    this.removeMeetingAction = this.element.querySelector('.removeMeetingAction');
    this.open = false;
    this.layer = 0;
    this.slots = [];
    this.scale = 1;

    if (this.agenda) {
        this.duration = this.agenda.getDuration(this.element.getAttribute('data-duration'));
        this.start = this.agenda.getRelativeTime(this.agenda.parseTime(this.element.getAttribute('data-beginhour')));
        this.end = this.start + this.duration;
    }

    if (null !== this.updateMeetingAction) {
        this.updateMeetingAction.addEventListener('click', function (event) {
            event.stopPropagation();
            event.preventDefault();
            this.handleRequestUpdateMeetingButton(event);
        }.bind(this), false);
    }

    if (null !== this.removeMeetingAction) {
        this.removeMeetingConfirmMessage = this.removeMeetingAction.getAttribute('data-confirm-remove-meeting-message');
        this.removeMeetingAction.addEventListener('click', function (event) {
            event.stopPropagation();
            event.preventDefault();
            this.handleRequestRemoveMeetingButton();
        }.bind(this), false);
    }

    this.toggleOpen = this.toggleOpen.bind(this);
    this.setLayer = this.setLayer.bind(this);

    if (this.element.classList.contains('has-details')) {
        this.header.addEventListener('click', this.toggleOpen);
    }
    this.element.agendaMeet = this;
    this.type = this.guessType();
}

Meet.prototype = Object.create(EventEmitter.prototype);
Meet.prototype.constructor = Meet;

/**
 * Margin: Top + Bottom padding of the ".meet" element.
 *
 * @type {Number}
 */
Meet.prototype.margin = 3;

Meet.prototype.handleRequestUpdateMeetingButton = function (event) {
    const href = event.currentTarget.getAttribute('href');

    $.get(href, function (response) {
        const modal = this.showModal(response);

        const participants = modal.find('form #update_meeting_participants input');

        if (participants.length === 0) {
            return;
        }

        const dataElement = modal.find('[data-available-slots]')
        const availableSlots = dataElement.data('available-slots');

        const that = this;
        participants.each(function () {
            this.dataAvailableSlots = availableSlots[this.getAttribute('data-participant-id')] || [];
            this.addEventListener('change', () => that.updateSlotsSelect(modal));
        });

        this.updateSlotsSelect(modal);

        if (availableSlots.length === 0) {
            return;
        }

        const slotSelect = modal.find('#update_meeting_meetingSlot')[0];
        slotSelect.addEventListener('change',
            (event) => this.updateParticipants(event.currentTarget, participants, modal));
        this.updateParticipants(slotSelect, participants, modal);

    }.bind(this))
        .fail(function () {
            alert('Operation not allowed.')
        });
};

Meet.prototype.updateSlotsSelect = function (modal) {
    let slotAvailableParticipantsCount = {};
    const selectedParticipant = modal.find('form #update_meeting_participants input:checked');
    selectedParticipant.each(function () {
        this.dataAvailableSlots.forEach((slotId) => {
            if (slotAvailableParticipantsCount[slotId]) {
                slotAvailableParticipantsCount[slotId]++;
            } else {
                slotAvailableParticipantsCount[slotId] = 1;
            }
        });
    });

    const slotOptions = modal.find('#update_meeting_meetingSlot option[data-meeting-slot-id]');
    slotOptions[0].parentElement.classList.remove('disabled');
    slotOptions.each(function () {
        const slotId = this.getAttribute('data-meeting-slot-id');
        this.disabled = !slotAvailableParticipantsCount[slotId] || (slotAvailableParticipantsCount[slotId] < selectedParticipant.length);
        if (this.disabled && this.selected) {
            this.parentElement.classList.add('disabled');
        }
    });

    this.updateValidateButton(modal);
}

Meet.prototype.updateParticipants = function (slotSelect, participants, modal) {
    const selectedOption = slotSelect[slotSelect.selectedIndex];
    const slotId = +selectedOption.getAttribute('data-meeting-slot-id');

    const unavailableLabelElement = modal.find('[data-template-label-unavailable]')[0];

    participants.each(function () {
        // check if slotId is in slots list for participant
        this.parentNode.querySelectorAll('[data-template-label-unavailable]').forEach((node) => node.remove());
        if (this.dataAvailableSlots.indexOf(slotId) !== -1) {
            this.classList.remove('unavailable');
            this.disabled = false;
        } else {
            this.classList.add('unavailable');
            const label = unavailableLabelElement.cloneNode(true);
            label.classList.remove('hide');
            this.parentNode.appendChild(label);
            this.checked = false;
            this.disabled = true;
        }
    });

    slotSelect.classList.remove('disabled');
    if (selectedOption.disabled) {
        slotSelect.classList.add('disabled');
    }

    this.updateValidateButton(modal);
}

Meet.prototype.updateValidateButton = function (modal) {
    const slotSelect = modal.find('#update_meeting_meetingSlot')[0];
    const selectedOption = slotSelect[slotSelect.selectedIndex];
    modal.find('[type="submit"]').attr('disabled', selectedOption.disabled);
}

Meet.prototype.handleRequestRemoveMeetingButton = function () {
    var href = this.removeMeetingAction.getAttribute('href');

    $.get(href, function (response) {
        this.showModal(response, true);
    }.bind(this))
        .fail(function () {
            alert('Operation not allowed.')
        });
};

Meet.prototype.showModal = function (html, confirmation = null) {
    var modal = $(this.modal);
    modal.modal().show();

    var content = modal.find('.modal-content');
    content.html(html);

    var form = content.find('form');

    $(form).on('submit', function (e) {
        e.preventDefault();

        if (confirmation) {
            if (!window.confirm(this.removeMeetingConfirmMessage)) {
                return false;
            }
        }

        this.handleRequestForm(form);

        return false;
    }.bind(this));

    return modal;
};

Meet.prototype.handleRequestForm = function (form)
{
    var submitButton = form.find("button[type='submit']");
    submitButton.attr('disabled', 'disabled');

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),
        success: function(){
            document.location.reload(true);
        },
        error: function(response) {
            alert(response.responseText);

            submitButton.removeAttr('disabled');
        }
    });

    return false;
};

/**
 * Display
 */
Meet.prototype.display = function () {
    this.element.style.top = this.getTop() + 'px';
    this.element.style.left = this.getLeft() + '%';
    this.element.style.width = this.getWidth() + '%';
    this.header.style.height = (this.getHeight() - this.margin) + 'px';

    this.toggleDisplay();
    this.updateScale();
};

Meet.prototype.toggleDisplay = function () {
    if (this.open) {
        this.element.classList.add('open');
        this.element.classList.remove('collapsed');
    } else {
        this.element.classList.remove('open');

        if (this.group && this.group.isExpanded()) {
            this.element.classList.add('collapsed');
        } else {
            this.element.classList.remove('collapsed');
        }
    }
};

/**
 * Toggle details
 *
 * @param {Event} event
 */
Meet.prototype.toggleOpen = function (event) {
    if (typeof event !== 'undefined') {
        event.preventDefault();
    }

    this.open = !this.open;

    if (!this.agenda) {
        this.toggleDisplay();
    }

    this.emit('change');
};

/**
 * Close
 */
Meet.prototype.close = function () {
    this.open = false;
};

/**
 * Update scale
 */
Meet.prototype.updateScale = function () {
    var scale = this.resolveScale();

    if (scale !== this.scale) {
        this.scale = scale;
        this.emit('scale');
    }
};

/**
 * Resovle scale
 *
 * @return {Number}
 */
Meet.prototype.resolveScale = function () {
    if (!this.open) {
        return 1;
    }

    return this.element.offsetHeight / (this.duration * this.agenda.scale);
};

/**
 * Is open
 *
 * @return {Boolean}
 */
Meet.prototype.isOpen = function () {
    return this.open;
};

/**
 * Get top position in pixel
 *
 * @return {Number}
 */
Meet.prototype.getTop = function () {
    return this.agenda.getY(this.start);
};

/**
 * Get top position in pixel
 *
 * @return {Number}
 */
Meet.prototype.getLeft = function () {
    if (!this.group) {
        return 0;
    }

    return this.group.getLayerLeft(this.layer);
};

/**
 * Get width in pixel
 *
 * @return {Number}
 */
Meet.prototype.getWidth = function () {
    if (!this.group) {
        return this.agenda.getMeetMaxWidth();
    }

    return this.group.getLayerWidth(this.layer);
};

/**
 * Get height in pixel
 *
 * @return {Number}
 */
Meet.prototype.getHeight = function () {
    return this.agenda.getY(this.end) - this.agenda.getY(this.start);
};

/**
 * Set group
 *
 * @param {Group} group
 */
Meet.prototype.setGroup = function (group) {
    this.group = group;
};

/**
 * Set layer
 *
 * @param {Number} layer
 */
Meet.prototype.setLayer = function (layer) {
    this.layer = layer;
};

/**
 * Meet overlap?
 *
 * @param {Meet} meet
 *
 * @return {Boolean}
 */
Meet.prototype.overlap = function (meet) {
    return this.timeOverlarp(meet.start, meet.end);
};

/**
 * Time overlap?
 *
 * @param {Date} from
 * @param {Date} to
 *
 * @return {Boolean}
 */
Meet.prototype.timeOverlarp = function (from, to) {
    return this.start < to && this.end > from;
};

Meet.prototype.guessType = function() {
    var classList = this.element.classList;

    if (classList.contains('program-mass')) {
        return 'mass'
    }

    if (classList.contains('happening')) {
        return 'happening';
    }

    if (classList.contains('lock')) {
        return 'unavailability';
    }

    // currently, "mass" & "happening" types have "has-details" class too so "meeting" need to be tested after them
    if (classList.contains('has-details')) {
        return 'meeting';
    }

    if (classList.contains('available')) {
        return 'availableForMeeting';
    }

    return 'unknown';
};

export default Meet;
