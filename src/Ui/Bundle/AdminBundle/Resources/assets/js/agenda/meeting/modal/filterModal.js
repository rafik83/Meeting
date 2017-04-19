var filterSheetForm = require('./../form/filterSheetForm'),
    options         = require('../../../vueComponents/options'),
    SheetFilter     = require('./../../filters/_SheetsFilter');

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
                hasScheduledMeetings: null,
                hasAvailableSlots: false,
                hasValidatedRequestNotScheduled: false
            },
            filters: {
                selectedTypes: [],
                selectedFollowers: [],
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false,
                hasSentMeetingRequest: null,
                hasScheduledMeetings: null,
                hasAvailableSlots: null,
                hasValidatedRequestNotScheduled: null
            }
        }
    },
    methods: {
        refreshList: function () {
            this.$emit('refresh-list', this.filteredSheets);
        },
        save: function () {
            this.setUsedFilter();
            this.filteredSheets = new SheetFilter(this.filters).filter(this.sheets);
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
                hasScheduledMeetings: null,
                hasAvailableSlots: null,
                hasValidatedRequestNotScheduled: null
            };
            this.formFilters =  {
                selectedTypes: [],
                selectedFollowers: [],
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false,
                hasSentMeetingRequest: null,
                hasScheduledMeetings: null,
                hasAvailableSlots: null,
                hasValidatedRequestNotScheduled: null
            }
        },
        setUsedFilter: function() {
            Object.assign(this.filters, this.formFilters);
        },
        setFormFilter: function () {
            Object.assign(this.formFilters, this.filters);
        }
    }
};
