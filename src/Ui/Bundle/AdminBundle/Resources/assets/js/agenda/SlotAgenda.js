var vue = require('vue'),
    options = require('../vueComponents/options'),
    AgendaApiEndpoints = require('../components/_AgendaApiEndpoints');

var api = new AgendaApiEndpoints();

module.exports = {
    template: '#slot-agenda',
    delimiters: options.delimiters,
    props: ['agendaSlot', 'sheet', 'participant'],
    data: function () {
        return {
            isMeetingToUpdateLoading: false,
            isAvailableForMeeting: false,
        }
    },
    methods: {
        select: function () {
            return this.transformRequestIntoMeeting();
        },

        transformRequestIntoMeeting: function () {
            this.$http.post(api.getTransformRequestIntoMeetingEndpoint(this.request), {
                slotId: this.agendaSlot.id
            }).then(function () {
                this.$emit('load-agenda', this.sheet);
            }.bind(this)).catch(function (error) {
                this.$emit('load-agenda', this.sheet);
                if (error.response) {
                    alert(error.response.data);
                } else {
                    alert(error.message);
                }
            }.bind(this));
        },

        /**
         * Load available slots for given meeting (slot) of participant and sheet
         */
        loadSlotsForMeeting: function () {
            if (null == this.agendaSlot.meetingId) {

                return;
            }

            this.$http.get(api.getMeetingUpdateSlotEndpoint(this.agendaSlot.meetingId))
                .then(function (response) {
                    this.availableSlotsForMeeting = response.data.availableSlotsId;
                    this.setSlotsStateAvailable();
                    this.$emit('refresh-agenda', this.sheet);
                }.bind(this))
                .catch(function (error) {
                    // this.cancelSlotAction();
                    this.$emit('refresh-agenda', this.sheet);

                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                }.bind(this));
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

            this.$http.get(api.getMeetingUpdateSpotEndpoint(this.agendaSlot.meetingId))
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
                    // this.loadAgenda(this.sheet);

                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                }.bind(this));
        },

        /**
         * Change slots state of given participant and given sheet
         */
        setSlotsStateAvailable: function () {
            if (null === this.participant.days) {
                return;
            }

            for (var dayIndex = 0; dayIndex < this.participant.days.length; dayIndex++) {
                for (var slotIndex = 0; slotIndex < this.participant.days[dayIndex].slots.length; slotIndex++) {
                    var currentSlot = this.participant.days[dayIndex].slots[slotIndex];

                    for (var availableSlotIndex = 0; availableSlotIndex < this.availableSlotsForMeeting.length; availableSlotIndex++) {
                        if (this.availableSlotsForMeeting[availableSlotIndex] === currentSlot.id) {
                            this.isAvailableForMeeting = true;
                            break;
                        }
                    }
                }
            }

            this.$forceUpdate();
        },

        /**
         * Remove meeting from sheet (and sheet met) and reload agenda(s)
         *
         * @param {string} message
         */
        removeMeeting: function (message) {
            if (window.confirm(message)) {
                this.$http.delete(api.getRemoveMeetingEndpoint(this.agendaSlot))
                    .then(function () {
                        this.$emit('focus-sheet', this.sheet);
                        this.$emit('refresh-agenda', this.sheet);
                    }.bind(this))
                    .catch(function (error) {
                        if (error.response) {
                            window.alert(error.response.data);
                        }
                    });
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
         * Select slot
         *
         * @param slot
         */
        selectSlot: function (slot) {

            if (false !== this.hasMeetingSlotToUpdate()) {

                return this.updateMeetingSlot(slot)
            }

            // if (null !== this.meetingRequestToTransformIntoMeeting) {
            //
            //     return this.transformRequestIntoMeeting(slot);
            // }

            //this.cancelSlotAction();
        },

        /**
         * Update meeting slot
         *
         * @param slot new selected slot
         */
        updateMeetingSlot: function (slot) {
            if (false === this.hasMeetingSlotToUpdate()) {

                return;
            }

            //this.cancelSlotAction();

            this.$http.post(api.getMeetingUpdateSlotEndpoint(this.agendaSlot.meetingId), {
                slotId: slot.id
            })
                .then(function () {
                    this.$emit('refresh-agenda', this.sheet);
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
         * Has meeting slot to update
         *
         * @returns {boolean}
         */
        hasMeetingSlotToUpdate: function () {
            return null !== this.agendaSlot && null !== this.sheet
        }
    }
};
