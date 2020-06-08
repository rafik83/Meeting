import Criteria from "./_Criteria";

/**
 * @param {array} types
 * @constructor
 */
function TypeCriteria(types) {
    this.types = types;
}

TypeCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
TypeCriteria.prototype.meetCriteria = function(sheets) {

    if (typeof this.types !== 'undefined' && this.types.length > 0) {
        return sheets.filter(function (sheet) {
            return this.types.indexOf(sheet.type) !== -1;
        }.bind(this));
    }

    return sheets;
};

export default TypeCriteria;
