var Criteria = require('./_Criteria');

function TypeCriteria(types){
    this.types = types;
}

TypeCriteria.prototype = new Criteria();

TypeCriteria.prototype.meetCriteria = function(sheets) {

    if (this.types.length > 0) {
        return sheets.filter(function (sheet) {
            return this.types.indexOf(sheet.type) !== -1;
        }.bind(this));
    }

    return sheets;
};

module.exports = TypeCriteria;
