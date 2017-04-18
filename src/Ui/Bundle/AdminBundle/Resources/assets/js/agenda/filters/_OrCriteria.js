var Criteria = require('./_Criteria');

/**
 * @param {Criteria} firstCriteria
 * @param {Criteria} secondCriteria
 * @constructor
 */
function OrCriteria(firstCriteria, secondCriteria) {
    this.firstCriteria = firstCriteria;
    this.secondCriteria = secondCriteria;
}

OrCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
OrCriteria.prototype.meetCriteria = function(sheets) {
    var firstCriteria = this.firstCriteria.meetCriteria(sheets);
    var secondCriteria = this.secondCriteria.meetCriteria(sheets);

    var len = firstCriteria.length;
    for (var i=0; i<len; i++) {
        if (!contains(secondCriteria, firstCriteria[i])){
            secondCriteria.push(firstCriteria[i]);
        }
    }

    return secondCriteria;
};

/**
 * @param {array} array
 * @param {object} element
 * @returns {boolean}
 */
function contains(array, element) {
    var arrayLength = array.length;
    for (var i=0; i < arrayLength; i++) {
        if (array[i] === element) {
            return true;
        }
    }

    return false;
}

module.exports = OrCriteria;
