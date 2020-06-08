import Criteria from "./_Criteria";

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

    noFollowerFilter = false;
    if (typeof this.followers !== 'undefined' && this.followers.length > 0) {

        this.followers.forEach(function (follower) {
            if (follower === 'follower_unassigned') {
                noFollowerFilter = true;

                return false;
            }
        });

        if (noFollowerFilter === false) {
            return [];
        }

        return sheets.filter(function (sheet) {
            return !sheet.hasFollower;
        });
    }

    return sheets;
};

export default NoFollowerCriteria;
