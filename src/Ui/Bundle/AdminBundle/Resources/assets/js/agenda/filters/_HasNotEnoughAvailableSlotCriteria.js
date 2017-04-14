var Criteria = require('./_Criteria');

function HasNotEnoughAvailableSlotCriteria(filter){
    this.filter = filter;
}

HasNotEnoughAvailableSlotCriteria.prototype = new Criteria();

HasNotEnoughAvailableSlotCriteria.prototype.meetCriteria = function(sheets) {

    if (this.filter === true) {
        return sheets.filter(function (sheet) {
            return sheet.hasNotEnoughAvailableSlot === this.filter;
        }.bind(this));
    }

    return sheets;
};

module.exports = HasNotEnoughAvailableSlotCriteria;
