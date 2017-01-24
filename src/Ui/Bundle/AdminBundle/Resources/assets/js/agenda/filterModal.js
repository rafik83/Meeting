var filterSheetForm = require('./filterSheetForm'),
    options         = require('../vueComponents/options');

module.exports = {
    template: '#filter-modal',
    delimiters: options.delimiters,
    props: ['sheets', 'show'],
    components: {
        'filter-sheet-form': filterSheetForm
    },
    data: function () {
        return {
            filteredSheets: [],
            formFilters: {
                selectedTypes: [],
                hasSentMeetingRequest: false,
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false
            },
            filters: {
                selectedTypes: [],
                hasSentMeetingRequest: false,
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false
            }
        }
    },
    methods: {
        refreshList: function () {
            this.$emit('refresh-list', this.filteredSheets);
        },
        save: function () {
            this.setUsedFilter();

            var filteredSheet = this.sheets;

            if (this.filters.selectedTypes.length > 0) {
                filteredSheet = filteredSheet.filter(function (sheet) {
                    return this.filters.selectedTypes.indexOf(sheet.type) !== -1;
                }.bind(this));
            }

            if (this.hasIndicatorFilter) {
                filteredSheet = filteredSheet.filter(function (sheet) {
                    if (this.filters.hasSentMeetingRequest === true
                        && sheet.hasSentMeetingRequest === this.filters.hasSentMeetingRequest) {
                        return true
                    }

                    if (this.filters.hasMeetingToApprove === true
                        && sheet.hasMeetingToApprove === this.filters.hasMeetingToApprove) {
                        return true
                    }

                    return this.filters.hasNotEnoughtAvailableSlot === true
                        && sheet.hasNotEnoughtAvailableSlot === this.filters.hasNotEnoughtAvailableSlot;
                }.bind(this));
            }

            this.filteredSheets = filteredSheet;
            this.$emit('refresh-list', this.filteredSheets);
            this.$emit('close-modal');
        },
        reset: function () {
            this.filters = {
                selectedTypes: [],
                hasSentMeetingRequest: false,
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false
            };
            this.formFilters =  {
                selectedTypes: [],
                hasSentMeetingRequest: false,
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false
            }
        },
        setUsedFilter: function() {
            Object.assign(this.filters, this.formFilters);
        },
        setFormFilter: function () {
            Object.assign(this.formFilters, this.filters);
        }
    },
    computed: {
        hasIndicatorFilter: function () {
            return this.filters.hasSentMeetingRequest === true
                || this.filters.hasMeetingToApprove === true
                || this.filters.hasNotEnoughtAvailableSlot === true;
        }
    }
};
