/**
 * Layer
 */
function Layer() {
    this.meets = [];
    this.expanded = false;

    this.onChange = this.onChange.bind(this);
}

/**
 * Add meet
 *
 * @param {Meet} meet
 */
Layer.prototype.add = function(meet) {
    this.meets.push(meet);

    meet.addEventListener('change', this.onChange);
};

/**
 * Count
 *
 * @return {Number}
 */
Layer.prototype.count = function() {
    return this.meets.length;
};

Layer.prototype.onChange = function() {
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
