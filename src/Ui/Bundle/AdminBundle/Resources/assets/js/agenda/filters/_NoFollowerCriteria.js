var Criteria = require('./_Criteria');

/**
 * @param {null|array} followers
 * @constructor
 */
function NoFollowerCriteria(followers) {
    this.followers = followers;
}

NoFollowerCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
NoFollowerCriteria.prototype.meetCriteria = function(sheets) {

    if (null === this.followers) {
        return sheets.filter(function (sheet) {
            return !sheet.hasFollower;
        }.bind(this));
    }

    return sheets;
};

module.exports = NoFollowerCriteria;
