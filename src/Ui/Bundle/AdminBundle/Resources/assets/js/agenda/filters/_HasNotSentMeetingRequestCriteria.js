var Criteria = require('./_Criteria');

function HasNotSentMeetingRequestCriteria(filter){
    this.filter = filter;
}

HasNotSentMeetingRequestCriteria.prototype = new Criteria();

HasNotSentMeetingRequestCriteria.prototype.meetCriteria = function(sheets) {

    if (this.filter === false) {
        return sheets.filter(function (sheet) {
            return sheet.hasNotSentMeetingRequest !== this.filter;
        }.bind(this));
    }

    return sheets;
};

module.exports = HasNotSentMeetingRequestCriteria;
