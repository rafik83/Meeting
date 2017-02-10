var slotAgenda = require('./SlotAgenda'),
    options = require('../vueComponents/options'),
    AgendaApiEndpoints = require('../components/_AgendaApiEndpoints');

var api = new AgendaApiEndpoints();

module.exports = {
    template: '#sheet-agenda',
    delimiters: options.delimiters,
    props: ['sheet', 'focused-sheet'],
    components: {
        'slot-agenda': slotAgenda
    },
    data: function () {
        return {
            availableSlotsForMeeting: [],
            currentAvailableSlotsForMeeting: [],
            slotToBeMoved: null
        }
    },
    methods: {
        init: function () {
            this.$emit('load-sheets');
        },

        focus: function () {
            this.$emit('focus-sheet', this.sheet);
        },

        /**
         * Event handler emit close-sheet-agenda event to close SheetAgenda
         */
        close: function () {
            this.$emit('close-sheet-agenda', this.sheet);
        },

        /**
         * @param {Object} slot
         */
        scheduleMeeting: function (slot) {
            this.$emit('schedule-meeting', slot);
        },

        /**
         * @param {Object} slot
         *
         * @returns {boolean}
         */
        isAvailableForMeeting: function (slot) {
            return slot.isAvailableForMeeting !== undefined ? slot.isAvailableForMeeting : false;
        },

        /**
         * @param {Object} meetingToUpdate
         */
        showMeetingUpdateModal: function (meetingToUpdate) {
            this.$emit('show-meeting-update-modal', meetingToUpdate);
        },

        /**
         * @param {int} massId
         */
        showMassAssignment: function (massId) {
            this.$emit('show-mass-assignment', massId);
        },

        /**
         * @param {Object} sheet
         */
        refreshAgenda: function (sheet) {
            this.$emit('refresh-agenda', sheet);
        },

        /**
         * @param {Object} event
         */
        handleRemoveMeeting: function (event) {
            this.$emit('remove-meeting', event);
        },

        handleMeetingMoved: function () {
            this.slotToBeMoved = null;
            this.refreshAgenda(this.sheet);
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
         * @param {Object} meetingRequest
         * @param {array} availableSlots
         *
         * @see loadSlotsForRequest
         */
        showAvailableSlotsForRequest: function (meetingRequest, availableSlots) {
            this.availableSlotsForMeeting = availableSlots;

            for (var index = 0; index < meetingRequest.participants.length; index++) {
                var participant = this.findParticipant(meetingRequest.participants[index]);
                this.setSlotsStateAvailable(participant);
            }
        },

        /**
         *
         * @param participant
         * @param availableSlots
         */
        showAvailableSlotsForMeeting: function (participant, availableSlots) {
            this.availableSlotsForMeeting = availableSlots;
            this.setSlotsStateAvailable(participant);
        },

        /**
         * Change slots state of given participant and given sheet
         *
         * @param {Object} participant
         */
        setSlotsStateAvailable: function (participant) {
            if (participant === null || null === participant.days) {
                return;
            }

            for (var dayIndex = 0; dayIndex < participant.days.length; dayIndex++) {
                for (var slotIndex = 0; slotIndex < participant.days[dayIndex].slots.length; slotIndex++) {
                    var currentSlot = participant.days[dayIndex].slots[slotIndex];

                    for (var availableSlotIndex = 0; availableSlotIndex < this.availableSlotsForMeeting.length; availableSlotIndex++) {
                        if (this.availableSlotsForMeeting[availableSlotIndex] === currentSlot.id) {
                            currentSlot.isAvailableForMeeting = true;
                            this.currentAvailableSlotsForMeeting.push(currentSlot);
                            break;
                        }
                    }
                }
            }

            this.$forceUpdate();
        },

        /**
         * Clear available slots
         */
        clearAvailableSlots: function () {
            this.currentAvailableSlotsForMeeting.forEach(function (slot) {
                slot.isAvailableForMeeting = false;
            });

            this.currentAvailableSlotsForMeeting = [];
            this.$forceUpdate();
        },

        /**
         * Find Participant agenda
         *
         * @param {Object} participant
         *
         * @returns null|{Object} participant
         */
        findParticipant: function (participant) {
            if (undefined === this.sheet.participants) {
                return null;
            }

            for (var i = 0; i < this.sheet.participants.length; i++) {
                if (this.sheet.participants[i].id === participant.id) {
                    return this.sheet.participants[i];
                }
            }

            return null;
        },

        forceUpdate: function () {
            this.$forceUpdate();
        },

        /**
         * Load available slots for given meeting (slot) of participant and sheet
         *
         * @param {Object} event
         */
        loadSlotsForMeeting: function (event) {
            if (null == event.slot.meetingId) {
                return;
            }

            this.$http.get(api.getMeetingUpdateSlotEndpoint(event.slot.meetingId))
                .then(function (response) {
                    this.slotToBeMoved = event.slot;

                    this.showAvailableSlotsForMeeting(
                        event.participant,
                        response.data.availableSlotsId
                    );
                }.bind(this))
                .catch(function (error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                }.bind(this));
        }
    }
};
