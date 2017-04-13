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
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false,
                hasSentMeetingRequest: null,
                hasScheduledMeetings: null,
                hasAvailableSlots: false
            },
            filters: {
                selectedTypes: [],
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false,
                hasSentMeetingRequest: null,
                hasScheduledMeetings: null,
                hasAvailableSlots: null
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
                    var filterMatching = false;

                    if (this.filters.hasMeetingToApprove === true
                        && sheet.hasMeetingToApprove === this.filters.hasMeetingToApprove) {
                        filterMatching = true;
                    }

                    if (this.filters.hasNotEnoughAvailableSlot === true
                        && sheet.hasNotEnoughAvailableSlot === this.filters.hasNotEnoughAvailableSlot) {
                        filterMatching = true;
                    }

                    if (this.filters.hasSentMeetingRequest === true
                        && sheet.hasNotSentMeetingRequest !== this.filters.hasSentMeetingRequest) {
                        filterMatching = true;
                    }

                    if (this.filters.hasSentMeetingRequest === false
                        && sheet.hasNotSentMeetingRequest !== this.filters.hasSentMeetingRequest) {
                        filterMatching = true;
                    }

                    if (this.filters.hasScheduledMeetings === true && (sheet.countPlacedMeetings > 0)) {
                        filterMatching = true;
                    }

                    if (this.filters.hasScheduledMeetings === false && sheet.countPlacedMeetings === 0) {
                        filterMatching = true;
                    }

                    if (this.filters.hasAvailableSlots === true && sheet.hasAvailableSlots === true) {
                        filterMatching = true;
                    }

                    return filterMatching;
                }.bind(this));
            }

            this.filteredSheets = filteredSheet;
            this.$emit('refresh-list', this.filteredSheets);
            this.$emit('close-modal');
        },
        reset: function () {
            this.filters = {
                selectedTypes: [],
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false,
                hasSentMeetingRequest: null,
                hasScheduledMeetings: null,
                hasAvailableSlots: null
            };
            this.formFilters =  {
                selectedTypes: [],
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false,
                hasSentMeetingRequest: null,
                hasScheduledMeetings: null,
                hasAvailableSlots: null
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
            return this.filters.hasMeetingToApprove === true
                || this.filters.hasNotEnoughAvailableSlot === true
                || this.filters.hasAvailableSlots === true
                || typeof this.filters.hasSentMeetingRequest === "boolean"
                || typeof this.filters.hasScheduledMeetings === "boolean"
        }
    }
};
