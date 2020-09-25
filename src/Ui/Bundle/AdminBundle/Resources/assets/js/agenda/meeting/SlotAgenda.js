import options from '../../vueComponents/options';
import AgendaApiEndpoints from "../../components/_AgendaApiEndpoints";
import eventDispatcher from '../../vueComponents/EventDispatcher';

var api = new AgendaApiEndpoints();

export default {
    template: '#slot-agenda',
    delimiters: options.delimiters,
    props: ['agendaSlot', 'sheet', 'participant', 'isAvailableForMeeting', 'highlight', 'slotToBeMoved'],
    data: function () {
        return {
            isMeetingToUpdateLoading: false,
            isActionButtonsEnabled: true
        }
    },
    methods: {
        /**
         * @returns {Number|null}
         */
        getSlotId: function() {
            return typeof this.agendaSlot.id !== 'undefined' ? this.agendaSlot.id : null;
        },

        /**
         * Move MeetingRequest or Meeting into available slot
         */
        scheduleMeeting: function () {
            if (this.slotToBeMoved !== null) {
                this.updateMeetingSlot(); // move meeting
            } else {
                this.$emit('schedule-meeting', this.agendaSlot); // move meeting request
            }
        },

        /**
         * Load available slots for given meeting (slot) of participant and sheet
         */
        loadSlotsForMeeting: function () {
            this.$emit('show-available-meeting-slot', {
                slot: this.agendaSlot,
                participant: this.participant
            });
        },

        /**
         * Load meeting's data and pass them to the MeetingUpdateModal using events
         *
         * @see MeetingUpdateModal
         */
        loadMeetingUpdateSpot: function () {
            if (this.agendaSlot.meetingId === undefined) {
                return;
            }

            this.isMeetingToUpdateLoading = true;

            this.$http
                .get(api.getMeetingUpdateSpotEndpoint(this.agendaSlot.meetingId))
                .then(function (response) {
                    var meetingToUpdate = {
                        sheet: this.sheet,
                        slot: this.agendaSlot,
                        form: response.data
                    };

                    this.$emit('show-meeting-update-modal', meetingToUpdate);
                    this.isMeetingToUpdateLoading = false;
                }.bind(this))
                .catch(function (error) {
                    this.isMeetingToUpdateLoading = false;
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                }.bind(this));
        },

        /**
         * Remove meeting from sheet (and sheet met) and reload agenda(s)
         *
         * @param {string} message
         */
        removeMeeting: function (message) {
            if (window.confirm(message)) {
                this.$emit('meeting-removing');

                this.$http
                    .delete(api.getRemoveMeetingEndpoint(this.agendaSlot))
                    .then(function () {
                        this.$emit('meeting-removed', {
                            sheet: this.sheet,
                            sheetMetId: this.agendaSlot.sheetMetId || null
                        });
                        this.$emit('focus-sheet', this.sheet);
                    }.bind(this))
                    .catch(function (error) {
                        this.$emit('meeting-removed-error');

                        if (error.response) {
                            window.alert(error.response.data);
                        }
                    }.bind(this));
            }
        },

        /**
         * Emit event to show agenda of given sheet id
         *
         * @param {int} sheetMetId
         */
        showAgendaForSheetId: function (sheetMetId) {
            this.$emit('show-agenda-for-sheet-id', sheetMetId);
        },

        /**
         * Move meeting from current slot to the selected available slot
         */
        updateMeetingSlot: function () {
            if (this.slotToBeMoved === null) {
                return false;
            }

            this.$emit('meeting-moving');

            this.$http
                .post(api.getMeetingUpdateSlotEndpoint(this.slotToBeMoved.meetingId), {
                    slotId: this.agendaSlot.id
                })
                .then(function () {
                    this.$emit('meeting-moved', {
                        sheet: this.sheet,
                        sheetMetId: this.slotToBeMoved.sheetMetId || null
                    });
                }.bind(this))
                .catch(function (error) {
                    this.$emit('refresh-agenda', this.sheet);
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                }.bind(this));
        },

        /**
         * @param {int} massId
         */
        showMassAssignment: function (massId) {
            this.$emit('show-mass-assignment', massId);
        },

        /**
         * Emit event to show agenda of given sheet id
         *
         * @param {int} spotId
         */
        loadSpotDetail: function (spotId) {
            eventDispatcher.dispatch('load-spot-detail', spotId);
            eventDispatcher.dispatch('toggleTab', 'spotTab');
        }
    }
};
