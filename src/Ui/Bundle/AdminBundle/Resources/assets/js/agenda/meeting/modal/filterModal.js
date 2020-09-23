import filterSheetForm from './../form/filterSheetForm';
import options from '../../../vueComponents/options';

export default {
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
                hasValidatedRequestNotScheduled: false,
                hasParticipantUnavailableWithMeetingRequest: false
            },
            filters: {
                selectedTypes: [],
                selectedFollowers: [],
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false,
                hasSentMeetingRequest: null,
                hasScheduledMeetings: null,
                hasAvailableSlots: null,
                hasValidatedRequestNotScheduled: null,
                hasParticipantUnavailableWithMeetingRequest: false
            }
        }
    },
    methods: {
        refreshList: function () {
            this.$emit('refresh-list', this.filteredSheets);
        },
        save: function () {
            this.setUsedFilter();
            this.$emit('filter-sheets', this.filters);
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
                hasValidatedRequestNotScheduled: null,
                hasParticipantUnavailableWithMeetingRequest: false
            };
            this.formFilters =  {
                selectedTypes: [],
                selectedFollowers: [],
                hasMeetingToApprove: false,
                hasNotEnoughAvailableSlot: false,
                hasSentMeetingRequest: null,
                hasScheduledMeetings: null,
                hasAvailableSlots: null,
                hasValidatedRequestNotScheduled: null,
                hasParticipantUnavailableWithMeetingRequest: false
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
