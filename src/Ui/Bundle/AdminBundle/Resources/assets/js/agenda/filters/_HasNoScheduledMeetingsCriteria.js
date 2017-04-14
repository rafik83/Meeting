var Criteria = require('./_Criteria');

function HasNoScheduledMeetingsCriteria(filter){
    this.filter = filter;
}

HasNoScheduledMeetingsCriteria.prototype = new Criteria();

HasNoScheduledMeetingsCriteria.prototype.meetCriteria = function(sheets) {

    if (this.filter === false) {
        return sheets.filter(function (sheet) {
            return sheet.countPlacedMeetings === 0;
        }.bind(this));
    }

    return sheets;
};

module.exports = HasNoScheduledMeetingsCriteria;
