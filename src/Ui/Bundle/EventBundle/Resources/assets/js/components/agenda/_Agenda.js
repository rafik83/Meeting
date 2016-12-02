var Meet = require('./_Meet');
var Planner = require('./_Planner');

/**
 * Agenda
 *
 * @param {Element} element
 */
function Agenda(element)
{
    this.element      = element;
    this.start        = this.getTime(this.element.getAttribute('data-beginhour'));
    this.end          = this.getTime(this.element.getAttribute('data-endhour'));
    this.duration     = this.diff(this.start, this.end);
    this.afternoon    = this.duration / 2;
    this.slotDuration = this.getDuration(this.element.getAttribute('data-slotduration'));
    this.scale        = this.slotHeight / this.slotDuration;
    this.meets        = [];
    this.planner      = new Planner(this.meets);

    this.addMeet = this.addMeet.bind(this);

    this.element.querySelectorAll('.meet').forEach(this.addMeet);

    this.planner.resolve();
};

/**
 * Slot height
 *
 * @type {Number}
 */
Agenda.prototype.slotHeight = 60;

/**
 * Add a meet
 *
 * @param {Element} element
 */
Agenda.prototype.addMeet = function(element)
{
    this.meets.push(new Meet(this, element));
};

/**
 * Get time from Element attribute
 *
 * @param {String} value
 *
 * @return {Date}
 */
Agenda.prototype.getTime = function(value)
{
    var data = value.split(':');
    var hour = data[0] || 0;
    var minutes = data[1] || 0;

    return hour * 60 + minutes * 1;
};

/**
 * Get duration from DomElement
 *
 * @param {String} value
 * @param {Date} start
 *
 * @return {Number}
 */
Agenda.prototype.getDuration = function(value, start)
{
    return this.diff(start || 0, this.getTime(value));
};

/**
 * Get time difference in minutes
 *
 * @param {Date|Number} from
 * @param {Date|Number} to
 *
 * @return {Number}
 */
Agenda.prototype.diff = function(from, to)
{
    return Math.round(to - from);
};

/**
 * Is the given Meet in the afternoon?
 *
 * @param {Number} time
 *
 * @return {Boolean}
 */
Agenda.prototype.isAfternoon = function(time)
{
    return time >= this.afternoon;
};

/**
 * Get time in pixel
 *
 * @param {Number} time
 *
 * @return {Number}
 */
Agenda.prototype.get = function(time) {
    return time * this.scale;
}

module.exports = Agenda;
