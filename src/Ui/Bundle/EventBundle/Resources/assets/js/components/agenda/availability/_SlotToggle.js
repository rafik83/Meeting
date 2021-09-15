import Toggle from "../../_Toggle";

/**
 * SlotToggle constructor
 *
 * @param {Availability} availability
 * @param {Slot} slot
 * @constructor
 */
function SlotToggle(availability, slot) {
    this.slot = slot;
    this.availability = availability;
    this.drawn = false;
    this.element = null;
    this.input = null;
    this.checked = null;
    this.toggle = null;

    this.availability.on('availability.enabled', this.show.bind(this));
    this.availability.on('availability.disabled', this.hide.bind(this));
    this.availability.on('availability.initialized', this.onAvailabilityInit.bind(this));
    this.slot.on('scale', this.scale.bind(this));
}

SlotToggle.prototype.onAvailabilityInit = function () {
    this.checked = this.guessIfCheckedFromSlot();
    this.availability.dayToggle.on('dayToggle.available', this.onDayToggleCheck.bind(this));
    this.availability.dayToggle.on('dayToggle.unavailable', this.onDayToggleUncheck.bind(this));
};

SlotToggle.prototype.onDayToggleCheck = function () {
    if (this.isLocked()) {
        return;
    }
    this.input.checked = true;
    this.onchange();
};

SlotToggle.prototype.onDayToggleUncheck = function () {
    if (this.isLocked()) {
        return;
    }
    this.input.checked = false;
    this.onchange();
};

SlotToggle.prototype.scale = function () {
    this.element.style.height = this.slot.getHeight() + 'px';
};

SlotToggle.prototype.show = function () {
    this.slot.displayMeets();
    if (this.drawn === false) {
        this.draw();
        return;
    }
    this.element.style.display = null;
};

SlotToggle.prototype.hide = function () {
    this.slot.displayMeets();
    if (this.drawn === false) {
        return;
    }
    this.element.style.display = 'none';
};

SlotToggle.prototype.draw = function () {
    var layout = this.availability.slotTogglesLayoutNode;

    this.element = document.createElement('div');

    this.element.className = 'slotToggle slotToggle--slot';

    this.input = document.createElement('input');
    this.input.setAttribute('type', 'checkbox');
    this.input.onchange = this.onchange.bind(this);
    this.element.appendChild(this.input);

    if (this.isLocked()) {
        this.input.setAttribute('disabled', true);
    }

    if (this.checked) {
        this.input.setAttribute('checked', true);
    }

    layout.appendChild(this.element);

    this.drawn = true;

    this.toggle = new Toggle(this.input);
};

SlotToggle.prototype.onchange = function () {
    this.checked = this.input.checked;
    this.toggle.refresh();
};

SlotToggle.prototype.guessIfCheckedFromSlot = function () {
    // first priority - in case of a slot have an unavailability and meeting
    if (this.slot.hasParticipantToBePresent()) {
        return true;
    }

    if (this.slot.hasUnavailabilities()) {
        return false;
    }

    return true;
};

SlotToggle.prototype.isLocked = function () {
    return this.slot.hasParticipantToBePresent();
};

SlotToggle.prototype.doesParticipantDeclareUnavailability = function () {
    if (this.isLocked()) {
        return false;
    }

    return this.checked !== true;
};

export default SlotToggle;
