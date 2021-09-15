import Criteria from "./_Criteria";

/**
 * @param {boolean} filter
 * @constructor
 */
function HasNotEnoughAvailableSlotCriteria(filter) {
    this.filter = filter;
}

HasNotEnoughAvailableSlotCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
HasNotEnoughAvailableSlotCriteria.prototype.meetCriteria = function(sheets) {

    if (this.filter === true) {
        return sheets.filter(function (sheet) {
            return sheet.hasNotEnoughAvailableSlot === this.filter;
        }.bind(this));
    }

    return sheets;
};

export default HasNotEnoughAvailableSlotCriteria;
