var Criteria = require('./_Criteria');

/**
 * @param {Criteria} criteria
 * @param {Criteria} secondCriteria
 * @constructor
 */
function OrCriteria(criteria, secondCriteria){
    this.criteria = criteria;
    this.secondCriteria = secondCriteria;
}

OrCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
OrCriteria.prototype.meetCriteria = function(sheets){
    var firstCriteria = this.criteria.meetCriteria(sheets);
    var secondCriteria = this.secondCriteria.meetCriteria(sheets);

    var len = firstCriteria.length;
    for(var i=0; i<len; i++){
        if(!contains(secondCriteria, firstCriteria[i])){
            secondCriteria.push(firstCriteria[i]);
        }
    }
    return secondCriteria;
};

/**
 * @param {array} arr
 * @param {object} ele
 * @returns {boolean}
 */
function contains(arr, ele){
    var len = arr.length;
    for(var i=0; i<len; i++){
        if(arr[i] === ele){
            return true;
        }
    }
    return false;
}

module.exports = OrCriteria;
