var sortSheetForm = require('./sortSheetForm'),
    options       = require('../vueComponents/options'),
    sortConstant  = {
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
    ascConstant  = [
        sortConstant.requestAsc,
        sortConstant.pendingRequestAsc,
        sortConstant.acceptedRequestAsc,
        sortConstant.scheduledMeetingAsc
    ],
    descConstant = [
        sortConstant.requestDesc,
        sortConstant.pendingRequestDesc,
        sortConstant.acceptedRequestDesc,
        sortConstant.scheduledMeetingDesc
    ]
;

module.exports = {
    template: '#sort-modal',
    delimiters: options.delimiters,
    props: ['sheets', 'show'],
    components: {
        'sort-sheet-form': sortSheetForm
    },
    data: function () {
        return {
            sortedSheets: [],
            formSort: {
                selected: false
            },
            sort: {
                selected: false
            }
        }
    },
    methods: {
        refreshList: function () {
            this.$emit('refresh-list', this.sortedSheets);
        },
        save: function () {
            this.setUsedSort();
            this.sortedSheets = this.sortSheets(this.sheets);

            this.$emit('refresh-list', this.sortedSheets);
            this.$emit('close-modal');
        },
        reset: function () {
            this.sort = {
                selected: false
            };
        },
        setUsedSort: function() {
            Object.assign(this.sort, this.formSort);
        },
        sortSheets: function (sheets) {
            var alphabeticalSort = [sortConstant.alphabeticalAsc, sortConstant.alphabeticalDesc];

            if (alphabeticalSort.indexOf(this.sort.selected) !== -1) {
                return this.alphabeticalSort(sheets);
            }

            return this.numericalSort(sheets);
        },
        alphabeticalSort: function(sheets) {

            sheets.sort(function (sheet1, sheet2) {
                if (sheet1.title < sheet2.title) return -1;
                if (sheet1.title > sheet2.title) return 1;
                return 0;
            });

            if (this.sort.selected === sortConstant.alphabeticalDesc) {
                sheets.reverse();
            }

            return sheets
        },
        numericalSort: function(sheets) {
            var valueToSort = this.getValueToSort(this.sort.selected);

            if (valueToSort !== null) {
                var way = sortConstant.asc;

                if (ascConstant.indexOf(this.sort.selected) === -1) {
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
        },
        getValueToSort: function (valueSelected) {
            if (valueSelected === sortConstant.requestAsc) {
                return "countRequest";
            } else if (valueSelected === sortConstant.pendingRequestAsc) {
                return "countPendingPropositions";
            } else if (valueSelected === sortConstant.acceptedRequestAsc) {
                return "countValidatedRequest";
            } else if (valueSelected === sortConstant.scheduledMeetingAsc) {
                return "countPlacedMeetings";
            } else if (valueSelected === sortConstant.requestDesc) {
                return "countRequest";
            } else if (valueSelected === sortConstant.pendingRequestDesc) {
                return "countPendingPropositions";
            } else if (valueSelected === sortConstant.acceptedRequestDesc) {
                return "countValidatedRequest";
            } else if (valueSelected === sortConstant.scheduledMeetingDesc) {
                return "countPlacedMeetings";
            }

            return null;
        }
    },
    watch: {
        selected: function(value) {
            this.sort.selected = value;
        }
    }
};
