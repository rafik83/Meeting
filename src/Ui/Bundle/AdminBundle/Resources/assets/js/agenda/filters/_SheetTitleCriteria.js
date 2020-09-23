import Criteria from "./_Criteria";

/**
 * @param {string} sheetTitle
 * @constructor
 */
function SheetTitleCriteria(sheetTitle) {
    this.filterSheetTitle = sheetTitle;
}

SheetTitleCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
SheetTitleCriteria.prototype.meetCriteria = function(sheets) {

    if (typeof this.filterSheetTitle !== 'undefined') {
        return sheets.filter(function (sheet) {
            return sheet.title.search(new RegExp(this.filterSheetTitle, 'i')) !== -1;
        }.bind(this));
    }

    return sheets;
};

export default SheetTitleCriteria;
