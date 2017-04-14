var Criteria = require('./_Criteria');

function HasSentMeetingRequestCriteria(filter){
    this.filter = filter;
}

HasSentMeetingRequestCriteria.prototype = new Criteria();

HasSentMeetingRequestCriteria.prototype.meetCriteria = function(sheets) {

    if (this.filter === true) {
        return sheets.filter(function (sheet) {
            return sheet.hasNotSentMeetingRequest !== this.filter;
        }.bind(this));
    }

    return sheets;
};

module.exports = HasSentMeetingRequestCriteria;
