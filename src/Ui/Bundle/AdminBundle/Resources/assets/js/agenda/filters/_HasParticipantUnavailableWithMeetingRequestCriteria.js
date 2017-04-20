var Criteria = require('./_Criteria');

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

    console.log('ici');
    console.log(this.filter);

    if (this.filter === true) {
        console.log('la');
        return sheets.filter(function (sheet) {
            return sheet.hasParticipantUnavailableWithMeetingRequest === true;
        }.bind(this));
    }

    return sheets;
};

module.exports = HasParticipantUnavailableWithMeetingRequestCriteria;
