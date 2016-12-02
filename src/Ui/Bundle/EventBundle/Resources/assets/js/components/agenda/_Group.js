/**
 * Group
 */
function Group()
{
    this.meets = [];
    this.layers = [];

    this.sortMeet = this.sortMeet.bind(this);
    this.resolveMeetLayer = this.resolveMeetLayer.bind(this);
}

/**
 * Count
 *
 * @return {Number}
 */
Group.prototype.countLayers = function() {
    return this.layers.length;
};

/**
 * Add a meet
 *
 * @param {Meet} meet
 */
Group.prototype.add = function(meet)
{
    if (this.meets.indexOf(meet) < 0) {
        this.meets.push(meet);
        meet.setGroup(this);
    }
};

/**
 * Resolve
 */
Group.prototype.resolve = function()
{
    this.layers.length = 0;
    this.meets.sort(this.sortMeet);
    this.meets.forEach(this.resolveMeetLayer);
};

/**
 * Sort meet by size
 *
 * @param {Meet} meetA
 * @param {Meet} meetB
 *
 * @return {Number}
 */
Group.prototype.sortMeet = function(meetA, meetB) {
    if (meetA.duration === meetB.duration) {
        return 0;
    }

    return meetA.duration > meetB.duration ? 1 : -1;
};

/**
 * Resolve Meet layers
 *
 * @param {Meet} meet
 */
Group.prototype.resolveMeetLayer = function(meet) {
    var layer = this.getLayer(meet);

    meet.setLayer(layer);

    this.layers[layer].push(meet);
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

    for (var overlap, layer, length, m, i = 0; i < layers; i++) {
        layer = this.layers[i];
        length = layer.length;
        overlap = false;

        for (m = 0; m < length; m++) {
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

module.exports = Group;
