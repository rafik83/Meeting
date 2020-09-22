import Criteria from "./_Criteria";

/**
 * @param {boolean} filter
 * @constructor
 */
function HasSentMeetingRequestCriteria(filter) {
    this.filter = filter;
}

HasSentMeetingRequestCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
HasSentMeetingRequestCriteria.prototype.meetCriteria = function(sheets) {

    if (this.filter === true) {
        return sheets.filter(function (sheet) {
            return sheet.hasNotSentMeetingRequest !== this.filter;
        }.bind(this));
    }

    return sheets;
};

export default HasSentMeetingRequestCriteria;
