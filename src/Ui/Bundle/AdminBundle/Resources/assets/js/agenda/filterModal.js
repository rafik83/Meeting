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
                selectedFollowers: [],
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false,
                hasSentMeetingRequest: null,
                hasScheduledMeetings: null
            },
            filters: {
                selectedTypes: [],
                selectedFollowers: [],
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false,
                hasSentMeetingRequest: null,
                hasScheduledMeetings: null
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

            if (this.filters.selectedFollowers.length > 0) {
                filteredSheet = filteredSheet.filter(function (sheet) {
                    var follower = sheet.followerFirstName + ' ' + sheet.followerLastName;

                    return this.filters.selectedFollowers.indexOf(follower) !== -1;
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
                selectedFollowers: [],
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false,
                hasSentMeetingRequest: null,
                hasScheduledMeetings: null
            };
            this.formFilters =  {
                selectedTypes: [],
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false,
                hasSentMeetingRequest: null,
                hasScheduledMeetings: null
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
                || this.filters.hasSentMeetingRequest === true
                || this.filters.hasSentMeetingRequest === false
                || this.filters.hasScheduledMeetings === true
                || this.filters.hasScheduledMeetings === false
        }
    }
};
