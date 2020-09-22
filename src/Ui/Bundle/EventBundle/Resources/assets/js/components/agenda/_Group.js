/**
 * Group
 */
function Group() {
    this.meets         = [];
    this.layers        = [];
    this.expandedLayer = null;

    this.sortMeet         = this.sortMeet.bind(this);
    this.resolveMeetLayer = this.resolveMeetLayer.bind(this);
    this.displayMeet      = this.displayMeet.bind(this);
    this.closeMeet        = this.closeMeet.bind(this);
    this.onChange         = this.onChange.bind(this);
}

/**
 * Layer collapsed width (in percent)
 *
 * @type {Number}
 */
Group.prototype.collapsedWidth = 10;

/**
 * Add a meet
 *
 * @param {Meet} meet
 */
Group.prototype.add = function(meet) {
    if (this.meets.indexOf(meet) < 0) {
        this.meets.push(meet);
        meet.setGroup(this);
        meet.on('change', this.onChange);
    }
};

/**
 * Get layer width
 *
 * @param {Number} index
 *
 * @return {Number}
 */
Group.prototype.getLayerWidth = function(index) {
    if (this.expandedLayer === null) {
        return this.getMeetMaxWidth() / this.layers.length;
    }

    if (this.expandedLayer === index) {
        return this.getMeetMaxWidth() - (this.collapsedWidth * (this.layers.length - 1));
    }

    return this.collapsedWidth;
};

/**
 * Get layer left
 *
 * @param {Number} index
 *
 * @return {Number}
 */
Group.prototype.getLayerLeft = function(index) {
    var left = 0;

    for (var l = 0; l < index; l++) {
       left += this.getLayerWidth(l);
    }

    return left;
};

/**
 * Resolve
 */
Group.prototype.resolve = function() {
    this.layers.length = 0;
    this.meets.sort(this.sortMeet);
    this.meets.forEach(this.resolveMeetLayer);
};

/**
 * Sort meet by size (and start date)
 *
 * @param {Meet} meetA
 * @param {Meet} meetB
 *
 * @return {Number}
 */
Group.prototype.sortMeet = function(meetA, meetB) {
    if (meetA.duration === meetB.duration) {
        if (meetA.start === meetB.start) {
            return 0;
        }

        return meetA.start < meetB.start ? -1 : 1;
    }

    return meetA.duration > meetB.duration ? -1 : 1;
};

/**
 * Resolve Meet layers
 *
 * @param {Meet} meet
 */
Group.prototype.resolveMeetLayer = function(meet) {
    var index = this.getLayer(meet);

    meet.setLayer(index);

    this.layers[index].push(meet);
};

/**
 * Get layer index for the given Meet
 *
 * @param {Meet} meet
 *
 * @return {Number}
 */
Group.prototype.getLayer = function(meet) {
    var layers = this.layers.length;

    for (var overlap, layer, length, i = 0; i < layers; i++) {
        layer   = this.layers[i];
        length  = layer.length;
        overlap = false;

        for (var m = 0; m < length; m++) {
            if (layer[m].overlap(meet)) {
                overlap = true;
                break;
            }
        }

        if (!overlap) {
            return i;
        }
    }

    this.layers.push([]);

    return layers;
};

/**
 * On change
 *
 * @param {Event} event
 */
Group.prototype.onChange = function(event) {
    var meet = event.target.agendaMeet;
    var open = meet.isOpen();

    if (open) {
        this.closeOther(meet);
    }

    this.expandedLayer = this.getOpenLayer();

    this.meets.forEach(this.displayMeet);
};

/**
 * Get open layer
 *
 * @return {Number|null}
 */
Group.prototype.getOpenLayer = function() {
    for (var i = this.meets.length - 1; i >= 0; i--) {
        if (this.meets[i].isOpen()) {
            return this.meets[i].layer;
        }
    }

    return null;
};

/**
 * Is any layer expanded?
 *
 * @return {Boolean}
 */
Group.prototype.isExpanded = function() {
    return this.expandedLayer !== null;
};

/**
 * Close other
 *
 * @param {Meet} meet
 */
Group.prototype.closeOther = function(meet) {
    for (var layer, index = this.layers.length - 1; index >= 0; index--) {
        if (meet.layer !== index) {
            this.layers[index].forEach(this.closeMeet);
        }
    }
};

/**
 * Display meet
 *
 * @param {Meet} meet
 */
Group.prototype.displayMeet = function(meet) {
    meet.display();
};

/**
 * Close meet
 *
 * @param {Meet} meet
 */
Group.prototype.closeMeet = function(meet) {
    meet.close();
};

Group.prototype.getMeetMaxWidth = function() {
    return this.meets[0].agenda.getMeetMaxWidth();
};

export default Group;
