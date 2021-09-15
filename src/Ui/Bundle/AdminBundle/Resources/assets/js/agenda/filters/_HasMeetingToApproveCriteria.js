import Criteria from "./_Criteria";

/**
 * @param {boolean} filter
 * @constructor
 */
function HasMeetingToApproveCriteria(filter) {
    this.filter = filter;
}

HasMeetingToApproveCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
HasMeetingToApproveCriteria.prototype.meetCriteria = function(sheets) {

    if (this.filter === true) {
        return sheets.filter(function (sheet) {
            return sheet.hasMeetingToApprove === this.filter;
        }.bind(this));
    }

    return sheets;
};

export default HasMeetingToApproveCriteria;
