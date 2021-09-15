import Criteria from "./_Criteria";

/**
 * @param {boolean} filter
 * @constructor
 */
function HasScheduledMeetingsCriteria(filter) {
    this.filter = filter;
}

HasScheduledMeetingsCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
HasScheduledMeetingsCriteria.prototype.meetCriteria = function(sheets) {

    if (this.filter === true) {
        return sheets.filter(function (sheet) {
            return sheet.countPlacedMeetings > 0;
        }.bind(this));
    }

    return sheets;
};

export default HasScheduledMeetingsCriteria;
