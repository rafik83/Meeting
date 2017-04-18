var Criteria = require('./_Criteria');

/**
 * @param {null|array} followers
 * @constructor
 */
function FollowerCriteria(followers) {
    this.followers = followers;
}

FollowerCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
FollowerCriteria.prototype.meetCriteria = function(sheets) {

    if (typeof this.types !== 'undefined' && this.followers.length > 0) {
        return sheets.filter(function (sheet) {
            var follower = sheet.followerFirstName + ' ' + sheet.followerLastName;

            return this.followers.indexOf(follower) !== -1;
        }.bind(this));
    }

    return sheets;
};

module.exports = FollowerCriteria;
