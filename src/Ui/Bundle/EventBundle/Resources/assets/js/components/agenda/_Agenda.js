import Meet from './_Meet';
import Slot from './_Slot';
import Planner from './_Planner';
import ResizeHandler from './_ResizeHandler';
import moment from 'moment';

/**
 * Agenda
 *
 * @param {Element} element
 */
function Agenda(element) {
    this.element      = element;
    this.agendaDate   = moment(this.element.querySelector('.agenda-date').getAttribute('data-date'), 'DD-MM-YY HH:mm:ss').format('MM-DD-YY');
    this.start        = this.parseTime(this.element.getAttribute('data-beginhour'));
    this.end          = this.parseTime(this.element.getAttribute('data-endhour'));
    this.startTimestamp = parseInt(element.getAttribute('data-beginTimestamp'));
    this.endTimestamp = parseInt(element.getAttribute('data-endTimestamp'));
    this.duration     = this.end - this.start;
    this.slotDuration = this.getDuration(this.element.getAttribute('data-slotduration'));
    this.layout       = this.element.querySelector('.layout');
    this.planner      = new Planner();
    this.resize       = new ResizeHandler(window);
    this.meets        = [];
    this.slots        = [];
    this.scale        = 0;
    this.slotHeight   = 0;
    this.meetMaxWidth = 100;

    this.moveMeetingModal = document.getElementById('meeting-modal');

    this.addMeet     = this.addMeet.bind(this);
    this.onSlotScale = this.onSlotScale.bind(this);
    this.onResize    = this.onResize.bind(this);
    this.updateMeet  = this.updateMeet.bind(this);

    for (var time = this.start; time <= this.end; time += this.slotDuration) {
        var slotDuration = this.end - time;
        this.addSlot(time, slotDuration);
    }
    Array.prototype.forEach.call(this.element.querySelectorAll('.meet'), this.addMeet);

    this.planner.setMeets(this.meets);

    if (moment(this.agendaDate, 'MM/DD/YY').format('MM/DD/YY') === moment().format('MM/DD/YY')) {
        [].forEach.call(this.slots, this.scrollOnSlot.bind(this));
    }

    this.resize.on('resized', this.onResize);
}

/**
 * Add slot
 *
 * @param {Number} time Time in minutes
 * @param {Number} slotDuration Duration in minutes
 */
Agenda.prototype.addSlot = function(time, slotDuration) {
    var slot = new Slot(this, time, slotDuration);
    this.slots.push(slot);
    this.layout.appendChild(slot.element);
    slot.on('scale', this.onSlotScale);
    this.setSlotHeight(slot.element.offsetHeight);
};

/**
 * Add a meet
 *
 * @param {Element} element
 */
Agenda.prototype.addMeet = function(element) {
    var meet   = new Meet(this, element, this.moveMeetingModal);
    var length = this.slots.length;

    this.meets.push(meet);

    for (var slot, i = 0; i < length; i++) {
        slot = this.slots[i];

        if (slot.match(meet.start, meet.end)) {
            slot.addMeet(meet);
        }
    }
};

/**
 * @param {Slot} slot
 */
Agenda.prototype.scrollOnSlot = function(slot) {
    var now = moment();
    var momentMidnight = now.clone().startOf('day');
    var currentTimeInMinutesFromMidnight = now.diff(momentMidnight, 'minutes');

    if (this.agendaDate === slot.dateAgenda
        && currentTimeInMinutesFromMidnight >= slot.time
        && currentTimeInMinutesFromMidnight <= (slot.time + slot.UIDuration)
        && slot.element.id ===  'm' + this.agendaDate + '-' + slot.time
    ) {
        window.location.hash = slot.element.id;

        // If there is an element on slot open it
        if (slot.meets.length > 0) {
            if (slot.meets[0].element.classList.contains('has-details')) {
                slot.meets[0].toggleOpen();
            }
        }
    }
};

/**
 * Set slot height
 *
 * @param {Number} slotHeight
 */
Agenda.prototype.setSlotHeight = function(slotHeight) {
    this.slotHeight = slotHeight;
    this.scale      = this.slotHeight / this.slotDuration;
};

/**
 * Get time from Element attribute
 *
 * @param {String} value
 *
 * @return {Number} (in minutes)
 */
Agenda.prototype.parseTime = function(value) {
    var data = value.split(':');
    var hour = data[0] || 0;
    var minutes = data[1] || 0;

    return hour * 60 + minutes * 1;
};

/**
 * Get time relative to the start of the agenda
 *
 * @param {Number} value
 *
 * @return {Number} (in minutes)
 */
Agenda.prototype.getRelativeTime = function(value) {
    return value - this.start;
};

/**
 * Get duration from DomElement
 *
 * @param {String} value
 *
 * @return {Number}
 */
Agenda.prototype.getDuration = function(value) {
    return this.parseTime(value);
};

/**
 * Get time in pixel
 *
 * @param {Number} time
 *
 * @return {Number}
 */
Agenda.prototype.getY = function(time) {
    var length = this.slots.length;
    var y = (time % this.slotDuration) * this.scale;
    var i = 0;

    while (i < length && time >= this.slots[i].end) {
        y += this.slots[i++].getHeight();
    }

    return y;
};

/**
 * Update meet scale
 *
 * @param {Meet} meet
 */
Agenda.prototype.updateMeet = function(meet) {
    meet.updateScale();
};

/**
 * On slot scale
 *
 * @param {Event} event
 */
Agenda.prototype.onSlotScale = function(event) {
    var slot   = event.target.agendaSlot;
    var length = this.slots.length;

    for (var i = this.slots.indexOf(slot); i < length; i++) {
        this.slots[i].displayMeets();
    }
};

/**
 * On window resized
 *
 * @param {Event} event
 */
Agenda.prototype.onResize = function(event) {
    this.meets.forEach(this.updateMeet);
};

Agenda.prototype.getMeetMaxWidth = function() {
    return this.meetMaxWidth;
};

export default Agenda;
