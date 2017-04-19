var Criteria = require('./_Criteria');

/**
 * @param {boolean} filter
 * @constructor
 */
function HasValidatedRequestNotScheduled(filter) {
    this.filter = filter;
}

HasValidatedRequestNotScheduled.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
HasValidatedRequestNotScheduled.prototype.meetCriteria = function(sheets) {

    if (this.filter === true) {
        return sheets.filter(function (sheet) {
            return sheet.hasValidatedRequestNotScheduled === this.filter;
        }.bind(this));
    }

    return sheets;
};

module.exports = HasValidatedRequestNotScheduled;
