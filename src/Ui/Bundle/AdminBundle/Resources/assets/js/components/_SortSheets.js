var sortConstant = {
    asc: 'asc',
    desc: 'desc',
    alphabeticalAsc: "alphabeticalAsc",
    alphabeticalDesc: "alphabeticalDesc",
    requestAsc: "requestAsc",
    pendingRequestAsc: "pendingRequestAsc",
    acceptedRequestAsc: "acceptedRequestAsc",
    scheduledMeetingAsc: "scheduledMeetingAsc",
    requestDesc: "requestDesc",
    pendingRequestDesc: "pendingRequestDesc",
    acceptedRequestDesc: "acceptedRequestDesc",
    scheduledMeetingDesc: "scheduledMeetingDesc"
},
    ascConstant = [
    sortConstant.requestAsc,
    sortConstant.pendingRequestAsc,
    sortConstant.acceptedRequestAsc,
    sortConstant.scheduledMeetingAsc
];

/**
 * @param {string} selectedSort
 * @constructor
 */
function SortSheets (selectedSort)
{
    this.selectedSort = selectedSort;
}

/**
 * @param {array} sheets
 * @returns {array}
 */
SortSheets.prototype.sort = function (sheets) {

    var alphabeticalSort = [sortConstant.alphabeticalAsc, sortConstant.alphabeticalDesc];

    if (alphabeticalSort.indexOf(this.selectedSort) !== -1) {
        return this.alphabeticalSort(sheets);
    }

    return this.numericalSort(sheets);
};


/**
 * @param {array} sheets
 * @returns {array}
 */
SortSheets.prototype.alphabeticalSort = function(sheets) {

    sheets.sort(function (sheet1, sheet2) {
        if (sheet1.title < sheet2.title) return -1;
        if (sheet1.title > sheet2.title) return 1;
        return 0;
    });

    if (this.selectedSort === sortConstant.alphabeticalDesc) {
        sheets.reverse();
    }

    return sheets
};

/**
 * @param {array} sheets
 * @returns {array}
 */
SortSheets.prototype.numericalSort = function(sheets) {
    var valueToSort = this.getValueToSort();

    if (valueToSort !== null) {
        var way = sortConstant.asc;

        if (ascConstant.indexOf(this.selectedSort) === -1) {
            way = sortConstant.desc;
        }

        sheets.sort(function (sheet1, sheet2) {
            if (way === sortConstant.asc) {
                return sheet1[valueToSort] - sheet2[valueToSort];
            } else if (way === sortConstant.desc) {
                return sheet2[valueToSort] - sheet1[valueToSort];
            }
        }.bind(this));
    }

    return sheets;
};

/**
 * @returns {null|string}
 */
SortSheets.prototype.getValueToSort = function () {
    if (this.selectedSort === sortConstant.requestAsc) {
        return "countRequest";
    } else if (this.selectedSort === sortConstant.pendingRequestAsc) {
        return "countPendingPropositions";
    } else if (this.selectedSort === sortConstant.acceptedRequestAsc) {
        return "countValidatedRequest";
    } else if (this.selectedSort === sortConstant.scheduledMeetingAsc) {
        return "countPlacedMeetings";
    } else if (this.selectedSort === sortConstant.requestDesc) {
        return "countRequest";
    } else if (this.selectedSort === sortConstant.pendingRequestDesc) {
        return "countPendingPropositions";
    } else if (this.selectedSort === sortConstant.acceptedRequestDesc) {
        return "countValidatedRequest";
    } else if (this.selectedSort === sortConstant.scheduledMeetingDesc) {
        return "countPlacedMeetings";
    }

    return null;
};

export default SortSheets;
