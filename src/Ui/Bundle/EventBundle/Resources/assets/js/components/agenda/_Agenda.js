var Meet = require('./_Meet');
var Slot = require('./_Slot');
var Planner = require('./_Planner');
var Scaler = require('./_Scaler');

/**
 * Agenda
 *
 * @param {Element} element
 */
function Agenda(element) {
    this.element      = element;
    this.start        = this.parseTime(this.element.getAttribute('data-beginhour'));
    this.end          = this.parseTime(this.element.getAttribute('data-endhour'));
    this.duration     = this.end - this.start;
    this.slotDuration = this.getDuration(this.element.getAttribute('data-slotduration'));
    this.layout       = this.element.querySelector('.layout');
    this.planner      = new Planner();
    this.scaler       = new Scaler();
    this.meets        = [];
    this.slots        = [];
    this.scale        = 0;
    this.slotHeight   = 0;

    this.addMeet     = this.addMeet.bind(this);
    this.onSlotScale = this.onSlotScale.bind(this);

    for (var time = this.start; time <= this.end; time += this.slotDuration) {
        this.addSlot(time);
    }

    this.setSlotHeight(this.slots[0].element.offsetHeight);

    this.element.querySelectorAll('.meet').forEach(this.addMeet);

    this.planner.setMeets(this.meets);
    this.scaler.setMeets(this.meets);
};

/**
 * Add slot
 *
 * @param {Number} time Time in minutes
 */
Agenda.prototype.addSlot = function(time) {
    var slot = new Slot(this, time, this.slotDuration);
    this.slots.push(slot);
    this.layout.appendChild(slot.element);
    slot.on('scale', this.onSlotScale);
};

/**
 * Add a meet
 *
 * @param {Element} element
 */
Agenda.prototype.addMeet = function(element) {
    var meet   = new Meet(this, element);
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

module.exports = Agenda;
