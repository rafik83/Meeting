var Criteria = require('./_Criteria');

function HasMeetingToApproveCriteria(filter){
    this.filter = filter;
}

HasMeetingToApproveCriteria.prototype = new Criteria();

HasMeetingToApproveCriteria.prototype.meetCriteria = function(sheets) {

    if (this.filter === true) {
        return sheets.filter(function (sheet) {
            return sheet.hasMeetingToApprove === this.filter;
        }.bind(this));
    }

    return sheets;
};

module.exports = HasMeetingToApproveCriteria;
