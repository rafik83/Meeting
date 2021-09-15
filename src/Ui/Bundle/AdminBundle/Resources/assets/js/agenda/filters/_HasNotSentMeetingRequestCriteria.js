import Criteria from "./_Criteria";

/**
 * @param {boolean} filter
 * @constructor
 */
function HasNotSentMeetingRequestCriteria(filter) {
    this.filter = filter;
}

HasNotSentMeetingRequestCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
HasNotSentMeetingRequestCriteria.prototype.meetCriteria = function(sheets) {

    if (this.filter === false) {
        return sheets.filter(function (sheet) {
            return sheet.hasNotSentMeetingRequest !== this.filter;
        }.bind(this));
    }

    return sheets;
};

export default HasNotSentMeetingRequestCriteria;
