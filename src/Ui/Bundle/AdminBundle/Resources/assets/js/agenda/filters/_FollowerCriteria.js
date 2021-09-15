import Criteria from "./_Criteria";

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

    if (typeof this.followers !== 'undefined' && this.followers.length > 0) {
        return sheets.filter(function (sheet) {
            var followerId = sheet.hasFollower === true ? sheet.follower.id : null;

            return this.followers.indexOf(followerId) !== -1;
        }.bind(this));
    }

    return sheets;
};

export default FollowerCriteria;
