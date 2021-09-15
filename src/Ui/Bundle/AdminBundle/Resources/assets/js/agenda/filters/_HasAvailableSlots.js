import Criteria from "./_Criteria";

/**
 * @param {boolean} filter
 * @constructor
 */
function HasAvailableSlots(filter) {
    this.filter = filter;
}

HasAvailableSlots.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
HasAvailableSlots.prototype.meetCriteria = function (sheets) {

    if (this.filter === true) {
        return sheets.filter(function (sheet) {
            return sheet.hasAvailableSlots === this.filter;
        }.bind(this));
    }

    return sheets;
};

export default HasAvailableSlots;
