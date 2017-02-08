var sortSheetForm = require('./sortSheetForm'),
    options       = require('../vueComponents/options');

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
                selected: false,
                alphabetical: false,
                request: false,
                pendingRequest: false,
                acceptedRequest: false,
                scheduledMeeting: false
            };
            this.formSort =  {
                alphabetical: false,
                request: false,
                pendingRequest: false,
                acceptedRequest: false,
                scheduledMeeting: false
            }
        },
        setUsedSort: function() {
            Object.assign(this.sort, this.formSort);
        },
        setFormSort: function () {
            Object.assign(this.formSort, this.sort);
        },
        sortSheets: function (sheets) {
            var alphabeticalSort = [
                "alphabeticalAsc",
                "alphabeticalDesc"
            ];

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

            if (this.sort.selected === "alphabeticalDesc") {
                sheets.reverse();
            }

            return sheets
        },
        numericalSort: function(sheets) {

            var ascSort = [
                "requestAsc",
                "pendingRequestAsc",
                "acceptedRequestAsc",
                "scheduledMeetingAsc"
            ];

            var descSort = [
                "requestDesc",
                "pendingRequestDesc",
                "acceptedRequestDesc",
                "scheduledMeetingDesc"
            ];

            sheets.sort(function (sheet1, sheet2) {

                if (ascSort.indexOf(this.sort.selected) !== -1) {
                    return sheet1.countRequest - sheet2.countRequest;
                }

                if (descSort.indexOf(this.sort.selected) !== -1) {
                    return sheet2.countRequest - sheet1.countRequest;
                }

            }.bind(this));

            return sheets;
        }
    },
    watch: {
        selected: function(value) {
            this.sort.selected = value;
        }
    }
};
