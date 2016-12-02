/**
 * Meet
 *
 * @param {Agenda} agenda
 * @param {Element} element
 */
function Meet(agenda, element)
{
    this.agenda    = agenda;
    this.element   = element;
    this.header    = this.element.querySelector('header');
    this.duration  = this.agenda.getDuration(this.element.getAttribute('data-duration'));
    this.start     = this.agenda.getTime(this.element.getAttribute('data-beginhour'));
    this.end       = this.start + this.duration;
    this.startTime = this.agenda.diff(this.agenda.start, this.start);
    this.afternoon = this.agenda.isAfternoon(this.startTime);
    this.layer     = 0;

    this.toggleOpen = this.toggleOpen.bind(this);
    this.setLayer = this.setLayer.bind(this);

    this.header.addEventListener('click', this.toggleOpen);

    this.display();
};

/**
 * Display
 */
Meet.prototype.display = function() {
    this.element.style.top = this.getTop() + 'px';
    this.element.style.left = this.getLeft() + '%';
    this.element.style.width = this.getWidth() + '%';
    this.element.style.height = this.getHeight() + 'px';
};

/**
 * Toggle details
 *
 * @param {Event} event
 */
Meet.prototype.toggleOpen = function(event)
{
    event.preventDefault();
    this.element.classList.toggle('open');
};

/**
 * Get top position in pixel
 *
 * @return {Number}
 */
Meet.prototype.getTop = function()
{
    return this.agenda.get(this.startTime) - (this.afternoon ? this.agenda.get(this.agenda.afternoon) : 0);
};

/**
 * Get top position in pixel
 *
 * @return {Number}
 */
Meet.prototype.getLeft = function()
{
    return this.layer * this.getWidth();
};

/**
 * Get width in pixel
 *
 * @return {Number}
 */
Meet.prototype.getWidth = function()
{
    var groups = this.group ? this.group.countLayers() : 1;

    return 100 / groups;
};

/**
 * Get height in pixel
 *
 * @return {Number}
 */
Meet.prototype.getHeight = function()
{
    return this.agenda.get(this.duration);
};

/**
 * Set group
 *
 * @param {Group} group
 */
Meet.prototype.setGroup = function(group) {
    this.group = group;
}

/**
 * Set layer
 *
 * @param {Number} layer
 */
Meet.prototype.setLayer = function(layer) {
    this.layer = layer;
    this.element.style.left = this.getLeft() + 'px';
}

/**
 * Meet overlap?
 *
 * @param {Meet} meet
 *
 * @return {Boolean}
 */
Meet.prototype.overlap = function(meet) {
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
Meet.prototype.timeOverlarp = function(from, to) {
    return this.start < to && this.end > from;
};

module.exports = Meet;
