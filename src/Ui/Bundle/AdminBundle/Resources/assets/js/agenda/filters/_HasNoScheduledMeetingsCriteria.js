import Criteria from "./_Criteria";

/**
 * @param {bool} filter
 * @constructor
 */
function HasNoScheduledMeetingsCriteria(filter) {
    this.filter = filter;
}

HasNoScheduledMeetingsCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
HasNoScheduledMeetingsCriteria.prototype.meetCriteria = function(sheets) {

    if (this.filter === false) {
        return sheets.filter(function (sheet) {
            return sheet.countPlacedMeetings === 0;
        }.bind(this));
    }

    return sheets;
};

export default HasNoScheduledMeetingsCriteria;
