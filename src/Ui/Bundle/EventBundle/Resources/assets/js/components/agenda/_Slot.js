import EventEmitter from "./_EventEmitter";

/**
 * Slot
 *
 * @param {Agenda} agenda Agenda
 * @param {Number} time Time (in minutes from midnight)
 * @param {Number} duration Duration in minutes
 */
function Slot(agenda, time, duration) {
    this.agenda   = agenda;
    this.dateAgenda = agenda.agendaDate;
    this.UIDuration = agenda.slotDuration;
    this.time     = time;
    this.start    = this.agenda.getRelativeTime(time);
    this.end      = this.start + agenda.slotDuration;
    this.meets    = [];
    this.scale    = 1;
    this.duration = duration;

    EventEmitter.call(this, this.createElement(time, this.dateAgenda));

    this.onMeetScale = this.onMeetScale.bind(this);
    this.displayMeet = this.displayMeet.bind(this);

    this.element.agendaSlot = this;
}

Slot.prototype = Object.create(EventEmitter.prototype);
Slot.prototype.constructor = Slot;

/**
 * Display
 */
Slot.prototype.display = function() {
    this.element.style.height = this.getHeight() + 'px';
};

/**
 * Get height in pixel
 *
 * @return {Number}
 */
Slot.prototype.getHeight = function() {
    return this.UIDuration * this.scale * this.agenda.scale;
};

/**
 * Resolve scale
 *
 * @return {Number}
 */
Slot.prototype.resolveScale = function() {
    var scale = 1;

    for (var i = this.meets.length - 1; i >= 0; i--) {
        scale = Math.max(scale, this.meets[i].scale);
    }

    return scale;
};

/**
 * Create element
 *
 * @param {Number} time Time in minutes
 * @param {string} agendaDate
 *
 * @return {Element}
 */
Slot.prototype.createElement = function(time, agendaDate) {
    var element = document.createElement('div');
    var label   = document.createElement('span');

    label.innerText   = this.format(time);
    element.className = 'insert';
    element.id =  'm' + agendaDate + '-' + time;
    element.appendChild(label);

    return element;
};

/**
 * Get time
 *
 * @param {Number} time Time in minutes
 *
 * @return {String}
 */
Slot.prototype.format = function(time) {
    return this.prefix(this.getHourFromTime(time)) + 'h' + this.prefix(this.getMinutesFromTime(time));
};

/**
 * Get hours from time
 *
 * @param {Number} time Time in minutes
 *
 * @return {Number}
 */
Slot.prototype.getHourFromTime = function(time) {
    return Math.floor(time / 60);
};

/**
 * Get minutes from time
 *
 * @param {Number} time Time in minutes
 *
 * @return {Number}
 */
Slot.prototype.getMinutesFromTime = function(time) {
    return Math.floor(time % 60);
};

/**
 * Prefix value < 10
 *
 * @param {Number} value
 *
 * @return {String}
 */
Slot.prototype.prefix = function(value) {
    return (value < 10 ? '0' : '') + value.toString();
};

/**
 * Add meet
 *
 * @param {Meet} meet
 */
Slot.prototype.addMeet = function(meet) {
    if (this.meets.indexOf(meet) < 0) {
        this.meets.push(meet);
        meet.on('scale', this.onMeetScale);
    }
};

/**
 * On meet change
 *
 * @param {Event} event
 */
Slot.prototype.onMeetScale = function(event) {
    var scale = this.resolveScale();

    if (scale !== this.scale) {
        this.scale = scale;
        this.display();
        this.emit('scale');
    }
};

/**
 * Does the given times match this slot?
 *
 * @param {Number} from Time in minutes
 * @param {Number} to Time in minutes
 *
 * @return {Boolean}
 */
Slot.prototype.match = function(from, to) {
    return this.start < to && this.end > from;
};

/**
 * Display all meets
 */
Slot.prototype.displayMeets = function() {
    this.meets.forEach(this.displayMeet);
};

/**
 * Display meet
 *
 * @param {Meet} meet
 */
Slot.prototype.displayMeet = function(meet) {
    meet.display();
};

Slot.prototype.hasParticipantToBePresent = function() {
    var result = false;
    [].forEach.call(this.meets, function(meet) {
        if (meet.type === 'meeting' || meet.type === 'happening') {
            result = true;
        }
    });

    return result;
};

Slot.prototype.hasUnavailabilities = function() {
    var result = false;
    [].forEach.call(this.meets, function(meet) {
        if (meet.type === 'unavailability') {
            result = true;
        }
    });

    return result;
};

export default Slot;
