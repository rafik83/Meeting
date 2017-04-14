var Criteria = require('./_Criteria');

function HasScheduledMeetingsCriteria(filter){
    this.filter = filter;
}

HasScheduledMeetingsCriteria.prototype = new Criteria();

HasScheduledMeetingsCriteria.prototype.meetCriteria = function(sheets) {

    if (this.filter === true) {
        return sheets.filter(function (sheet) {
            return sheet.countPlacedMeetings > 0;
        }.bind(this));
    }

    return sheets;
};

module.exports = HasScheduledMeetingsCriteria;
