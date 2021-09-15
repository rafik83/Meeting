import EventEmitter from "../_EventEmitter";
import Toggle from "../../_Toggle";

/**
 * DayToggle constructor
 *
 * @param {Availability} availability
 * @constructor
 */
function DayToggle(availability) {
    this.availability = availability;
    this.drawn = false;
    this.element = document.createElement('div');
    this.input = null;
    this.checked = null;
    this.toggle = null;

    this.availability.on('availability.enabled', this.show.bind(this));
    this.availability.on('availability.disabled', this.hide.bind(this));
    this.availability.on('availability.initialized', this.onAvailabilityInit.bind(this));

    EventEmitter.call(this, this.element);
}

DayToggle.prototype = Object.create(EventEmitter.prototype);
DayToggle.prototype.constructor = DayToggle;

DayToggle.prototype.scale = function () {
    this.element.style.height = this.slot.getHeight() + 'px';
};

DayToggle.prototype.onAvailabilityInit = function () {
    this.checked = this.guessIfCheckedFromAvailability();
    this.prepareNodes();
};

DayToggle.prototype.show = function () {
    if (this.drawn === false) {
        this.draw();
        return;
    }
    this.element.style.display = null;
};

DayToggle.prototype.hide = function () {
    if (this.drawn === false) {
        return;
    }
    this.element.style.display = 'none';
};

DayToggle.prototype.prepareNodes = function () {
    this.input = document.createElement('input');
    this.element.className = 'slotToggle';
    this.input.setAttribute('type', 'checkbox');
    this.input.onchange = this.onCheckboxChange.bind(this);
    this.element.appendChild(this.input);
    if (this.isLocked()) {
        this.input.setAttribute('disabled', true);
    }

    if (this.checked) {
        this.input.setAttribute('checked', true);
    }
};

DayToggle.prototype.draw = function () {
    var layout = this.availability.dayToggleLayoutNode;

    layout.appendChild(this.element);

    this.drawn = true;

    this.toggle = new Toggle(this.input);
};

DayToggle.prototype.onCheckboxChange = function () {
    this.checked = this.input.checked;
    if (this.input.checked) {
        this.emit('dayToggle.available');
    } else {
        this.emit('dayToggle.unavailable');
    }
};

DayToggle.prototype.guessIfCheckedFromAvailability = function () {
    return !this.availability.isParticipantFullyUnavailable();
};

DayToggle.prototype.isLocked = function () {
    return this.availability.hasParticipantToBePresent();
};

DayToggle.prototype.doesParticipantDeclareUnavailability = function () {
    if (this.isLocked()) {
        return false;
    }

    return this.checked !== true;
};

export default DayToggle;
