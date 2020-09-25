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
    this.moveMeetingAction = this.element.querySelector('.moveMeetingAction');
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

    if (null !== this.moveMeetingAction) {
        this.moveMeetingAction.addEventListener('click', function (event) {
            event.stopPropagation();
            event.preventDefault();
            this.handleRequestMoveMeetingButton();
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

Meet.prototype.handleRequestMoveMeetingButton = function () {
    var href = this.moveMeetingAction.getAttribute('href');

    $.get(href, function (response) {
        this.showModal(response);
    }.bind(this))
        .fail(function () {
            alert('Operation not allowed.')
        });
};

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
