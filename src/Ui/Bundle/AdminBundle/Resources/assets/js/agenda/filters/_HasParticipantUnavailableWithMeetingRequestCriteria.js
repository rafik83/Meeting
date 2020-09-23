import Criteria from "./_Criteria";

/**
 * @param {boolean} filter
 * @constructor
 */
function HasParticipantUnavailableWithMeetingRequestCriteria(filter) {
    this.filter = filter;
}

HasParticipantUnavailableWithMeetingRequestCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 *
 * @returns {array}
 */
HasParticipantUnavailableWithMeetingRequestCriteria.prototype.meetCriteria = function(sheets) {

    if (this.filter === true) {
        return sheets.filter(function (sheet) {
            return sheet.hasParticipantUnavailableWithMeetingRequest === true;
        }.bind(this));
    }

    return sheets;
};

export default HasParticipantUnavailableWithMeetingRequestCriteria;
