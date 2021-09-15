import SlotAgenda from './SlotAgenda';
import options from '../../vueComponents/options';
import AgendaApiEndpoints from "../../components/_AgendaApiEndpoints";

var api = new AgendaApiEndpoints();

export default {
    template: '#sheet-agenda',
    delimiters: options.delimiters,
    props: ['sheet', 'focused-sheet'],
    components: {
        'slot-agenda': SlotAgenda
    },
    data: function () {
        return {
            availableSlotsForMeeting: [],
            currentAvailableSlotsForMeeting: [],
            slotToBeMoved: null,
            routeToUpdateSheetAttendance: null,
        }
    },
    beforeMount: function () {
        this.routeToUpdateSheetAttendance = api.updateSheetAttendance(this.sheet.id);
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
            this.disableSlotsAction();
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
        handleMeetingRemoved: function (event) {
            this.$emit('refresh-both-agenda', event);
        },

        /**
         * This method disable the slot action when the slot is being removed
         */
        handleMeetingRemoving: function () {
            this.disableSlotsAction();
        },

        /**
         * Reload the agenda on meeting removed error
         */
        handleMeetingRemovedError: function() {
            this.$emit('refresh-agenda', sheet);
        },

        /**
         * @param {Object} event
         */
        handleMeetingMoved: function (event) {
            this.slotToBeMoved = null;
            this.$emit('refresh-both-agenda', event);
        },

        /**
         * This method disable the slot action when the slot is moving
         */
        handleMeetingMoving: function () {
            this.disableSlotsAction();
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
                this.setSlotActionButtonsState(false); // disabled slot action buttons
            }
        },

        /**
         * @param {Object} participant
         * @param {array} availableSlots
         */
        showAvailableSlotsForMeeting: function (participant, availableSlots) {
            this.availableSlotsForMeeting = availableSlots;
            this.setSlotsStateAvailable(participant);
            this.setSlotActionButtonsState(false);
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

        disableSlotsAction: function () {
            this.currentAvailableSlotsForMeeting.forEach(function (slot) {
                slot.isAvailableForMeeting = false;
            });

            this.currentAvailableSlotsForMeeting = [];

            this.setSlotActionButtonsState(false); // disabled slot action buttons
        },

        /**
         * Clear available slots
         */
        clearAvailableSlots: function () {
            this.disableSlotsAction();
            this.setSlotActionButtonsState(true);
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
        },

        /**
         * Enable or Disable AgendaSlot action buttons
         *
         * @param {boolean} state
         * @see SlotAgenda
         */
        setSlotActionButtonsState: function (state) {
            var childs = this.$refs.childSlotAgenda;

            if (childs !== undefined) {
                for (var i = 0; i < childs.length; i++) {
                    childs[i].isActionButtonsEnabled = state;
                }
            }
        },

        /**
         * Enable or Disable AgendaSlot action buttons for given slotId
         *
         * @param {boolean} state
         * @param {Number}  slotId
         * @see SlotAgenda
         */
        setSlotActionButtonsStateForSlotId: function (state, slotId) {
            var childs = this.$refs.childSlotAgenda;

            if (typeof childs !== 'undefined') {
                for (var i = 0; i < childs.length; i++) {
                    if (childs[i].getSlotId() === slotId) {
                        childs[i].isActionButtonsEnabled = state;
                    }
                }
            }
        }
    }
};
