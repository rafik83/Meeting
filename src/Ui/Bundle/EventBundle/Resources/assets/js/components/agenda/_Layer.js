/**
 * Layer
 */
function Layer() {
    this.meets    = [];
    this.expanded = false;

    this.onMeetChange = this.onMeetChange.bind(this);
}

/**
 * Add meet
 *
 * @param {Meet} meet
 */
Layer.prototype.add = function(meet) {
    this.meets.push(meet);
    meet.on('change', this.onMeetChange);
};

/**
 * Count
 *
 * @return {Number}
 */
Layer.prototype.count = function() {
    return this.meets.length;
};

/**
 * On meet change
 *
 * @param {Event} event
 */
Layer.prototype.onMeetChange = function(event) {
    var expanded = this.isExpandedMeet();

    if (this.expanded !== expanded) {
        this.expanded = expanded;
    }
}

/**
 * Is any Meet expanded?
 *
 * @return {Boolean}
 */
Layer.prototype.isExpandedMeet = function() {
    for (var i = this.meets.length - 1; i >= 0; i--) {
        if (this.meets[i].isOpen()) {
            return true;
        }
    }

    return false;
};

module.exports = Layer;
