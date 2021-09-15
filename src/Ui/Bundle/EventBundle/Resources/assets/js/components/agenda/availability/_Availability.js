import EventEmitter from "../_EventEmitter";
import SlotToggle from "./_SlotToggle";
import DayToggle from "./_DayToggle";

/**
 * Availability constructor
 *
 * @param {Agenda} agenda
 * @param {HTMLElement} slotTogglesLayoutNode
 * @param {HTMLElement} dayToggleLayoutNode
 * @constructor
 */
function Availability(agenda, slotTogglesLayoutNode, dayToggleLayoutNode) {
    EventEmitter.call(this, agenda.element);
    this.agenda = agenda;
    this.disable();
    this.slotsInitialized = false;
    this.slots = [];
    this.dayToggle = null;
    this.slotTogglesLayoutNode = slotTogglesLayoutNode;
    this.dayToggleLayoutNode = dayToggleLayoutNode;
}

Availability.prototype = Object.create(EventEmitter.prototype);
Availability.prototype.constructor = Availability;

Availability.prototype.enable = function () {
    this.enabled = true;
    this.initToggles();
    this.agenda.meetMaxWidth = 80;
    this.emit('availability.enabled');
};

Availability.prototype.disable = function () {
    this.enabled = false;
    this.agenda.meetMaxWidth = 100;
    this.emit('availability.disabled');
};

Availability.prototype.initToggles = function () {
    if (this.slotsInitialized === true) {
        return;
    }

    for (const agendaSlot of this.agenda.slots) {
        if (agendaSlot.duration > 0) {
            this.slots.push(new SlotToggle(this, agendaSlot));
        }
    }

    this.dayToggle = new DayToggle(this);

    this.emit('availability.initialized');

    this.slotsInitialized = true;
};

Availability.prototype.hasParticipantToBePresent = function () {
    var result = false;
    [].forEach.call(this.slots, function (slot) {
        result = result || slot.slot.hasParticipantToBePresent();
    });

    return result;
};

Availability.prototype.isParticipantFullyUnavailable = function () {
    if (this.hasParticipantToBePresent()) {
        return false;
    }

    var oneCheck = false;
    [].forEach.call(this.slots, function (slot) {
        oneCheck = oneCheck || slot.checked;
    });

    return !oneCheck;
};

export default Availability;
