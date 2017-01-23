var Vue                = require('vue'),
    axios              = require('axios'),
    AgendaApiEndpoints = require('./components/_AgendaApiEndpoints');

var agendaApiEndpoints = new AgendaApiEndpoints();

/**
 * Customs delimiters to avoid collision with Twig
 */
var delimiters = ['${', '}'];

/**
 * Pass axios to Vue
 */
Vue.prototype.$http = axios;

Vue.component('Modal', {
    template: '#modal-template',
    props: ['show'],
    methods: {
        close: function () {
            this.$emit('close-modal');
        }
    }
});

Vue.component('MeetingUpdateModal', {
    delimiters: delimiters,
    template: '#meeting-update-modal-template',
    props: {
        meetingToUpdate: {
            type: Object,
            default: function () {
                return {
                    form: {
                        meetingId: null,
                        blockedSlot: false,
                        blockedSpot: false,
                        spotId: null,
                        availableSpots: []
                    }
                }
            }
        }
    },
    data: function () {
        return {
            disabled: false
        }
    },
    methods: {
        reinit: function () {
            this.disabled = false;
        },
        close: function () {
            this.$emit('close-modal');
            this.reinit();
        },
        save: function () {
            this.disabled = true;

            this.$http.post(agendaApiEndpoints.getMeetingUpdateSpotEndpoint(this.meetingToUpdate.form.meetingId), {
                blockedSlot: this.meetingToUpdate.form.blockedSlot,
                blockedSpot: this.meetingToUpdate.form.blockedSpot,
                spotId: this.meetingToUpdate.form.spotId
            })
            .then(function (response) {
                this.$emit('meeting-updated');
                this.close();
            }.bind(this))
            .catch(function (error) {
                if (error.response) {
                    alert(error.response.data);
                } else {
                    alert(error.message);
                }

                this.disabled = false;
            }.bind(this));
        }
    }
});

new Vue({
    el: '#agenda',
    delimiters: delimiters,
    data: {
        /**
         * Array of sheets
         */
        sheets: [],

        /**
         * opened sheet
         */
        agendas: [],

        /**
         * Sheet focused
         */
        focus: null,

        /**
         * Is meeting loading
         */
        isMeetingToUpdateLoading: false,

        /**
         * Meeting to update form
         */
        meetingToUpdate: null,

        /**
         * Meeting slot to update
         */
        meetingSlotToUpdate: null,

        /**
         * Available slots for meeting
         */
        availableSlotsForMeeting: []
    },

    /**
     * When Vue app is ready
     */
    mounted: function () {
        this.$nextTick(function () {
            this.init();
        });
    },

    /**
     * Computed values
     */
    computed: {
        noRequestsInFocus: function () {
            return focus.requests == undefined || focus.requests.length === 0;
        },
        noSheets: function () {
            return this.sheets.length === 0
        }
    },

    methods: {
        /**
         * Init methods
         */
        init: function () {
            this.loadSheets();
        },

        /**
         * Load sheets data
         */
        loadSheets: function () {
            this.$http.get(agendaApiEndpoints.getSheetsEndpoint())
                .then(function(response) {
                    this.sheets = response.data;
                }.bind(this))
                .catch(function(error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                });
        },

        /**
         * Clear sheet agenda data
         *
         * @param sheet
         */
        clearAgenda: function (sheet) {
            var sheetId = this.findSheetAgenda(sheet);

            if (-1 >= sheetId) {
                return;
            }

            this.agendas[sheetId].participants = [];
            this.agendas[sheetId].requests = [];
        },

        /**
         * Load sheet agenda data
         *
         * @param sheet
         */
        loadAgenda: function (sheet) {
            var sheetId = this.findSheetAgenda(sheet);

            if (-1 === sheetId) {
                return;
            }

            this.$http.get(agendaApiEndpoints.getSheetAgendaEndpoint(sheet))
                .then(function(response) {
                    var participants = response.data.participants;
                    var requests = response.data.requests;
                    this.clearAgenda(sheet);

                    participants.forEach(function (participant) {
                        this.agendas[sheetId].participants.push(participant);
                    }.bind(this));

                    requests.forEach(function (request) {
                        this.agendas[sheetId].requests.push(request);
                    }.bind(this));

                    this.highlightMeetingsInCommon(sheet, true);
                    this.$forceUpdate();
                }.bind(this))
                .catch(function(error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                });
        },

        /**
         * Show and focus agenda of given sheet
         *
         * @param sheet
         */
        showAndFocusAgenda: function (sheet) {
            this.showAgenda(sheet);
            this.focusAgenda(sheet);
        },

        /**
         * Show agenda of given sheet
         *
         * @param sheet
         */
        showAgenda: function (sheet) {
            if(-1 === this.findSheetAgenda(sheet)) {
                this.agendas.push(sheet);
            }

            this.highlightMeetingsInCommon(sheet, true);
            this.loadAgenda(sheet);
        },

        /**
         * Show agenda of given sheet id
         *
         * @param {int} sheetMetId
         */
        showAgendaForSheetId: function (sheetMetId) {
            var sheet = this.findSheetBySheetId(sheetMetId);

            if (null === sheet) {
                alert('Sheet id = ' + sheetMetId + ' not found');
                return;
            }

            this.showAgenda(sheet);
        },

        /**
         * Find index of a sheet
         *
         * @param sheet
         * @returns {Number}
         */
        findSheet: function (sheet) {
            return this.sheets.indexOf(sheet);
        },

        /**
         * Find index of sheet in agendas
         *
         * @param sheet
         * @returns {Number}
         */
        findSheetAgenda: function (sheet) {
            return this.agendas.indexOf(sheet);
        },

        /**
         * Find Sheet or returns null
         *
         * @param {int} sheetId
         * @returns null|sheet
         */
        findSheetBySheetId: function (sheetId) {
            for (var sheetIndex = 0; sheetIndex < this.sheets.length; sheetIndex++) {
                if (this.sheets[sheetIndex].id === sheetId) {
                    return this.sheets[sheetIndex];
                }
            }

            return null;
        },

        /**
         * Find Sheet in opened Agendas or returns null
         *
         * @param {int} sheetId
         * @returns null|sheet
         */
        findSheetAgendaBySheetId: function (sheetId) {
            for (var agendaIndex = 0; agendaIndex < this.agendas.length; agendaIndex++) {
                if (this.agendas[agendaIndex].id === sheetId) {
                    return this.agendas[agendaIndex];
                }
            }

            return null;
        },

        /**
         * Close given sheet agenda
         *
         * @param sheet
         */
        closeAgenda: function (sheet) {
            this.highlightMeetingsInCommon(sheet, false);
            this.agendas.splice(this.findSheetAgenda(sheet), 1);

            if (this.focus == sheet) {
                this.focus = null;
            }
        },

        /**
         * Focus to a given sheet agenda
         *
         * @param sheet
         */
        focusAgenda: function (sheet) {
            if(-1 === this.findSheetAgenda(sheet)) {
                return;
            }

            this.focus = sheet;
        },

        /**
         * Find meetings for the given sheet
         *
         * @param sheet
         * @returns {Array} of meeting slots
         */
        findMeetings: function (sheet) {
            var sheetId      = this.findSheetAgenda(sheet);
            var participants = this.agendas[sheetId].participants;

            if (this.agendas[sheetId].participants === undefined) {
                return [];
            }

            var meetings = [];

            for (var participantIndex = 0; participantIndex < participants.length; participantIndex++) {
                for (var dayIndex = 0; dayIndex < participants[participantIndex].days.length; dayIndex++) {
                    for (var slotIndex = 0; slotIndex < participants[participantIndex].days[dayIndex].slots.length; slotIndex++) {
                        var slot = participants[participantIndex].days[dayIndex].slots[slotIndex];

                        if ('meeting_unavailability' === slot.type) {
                            meetings.push(slot);
                        }
                    }
                }
            }

            return meetings;
        },

        /**
         * Find Participant agenda
         *
         * @param sheet
         * @param participant
         *
         * @returns null|participant
         */
        findParticipantAgenda: function (sheet, participant) {
            var sheetId      = this.findSheetAgenda(sheet);

            if (this.agendas[sheetId].participants === undefined) {
                return null;
            }

            var index = this.agendas[sheetId].participants.indexOf(participant);

            if (-1 === index) {
                return null;
            }

            return this.agendas[sheetId].participants[index];
        },

        /**
         * Highlight meetings in common in opened agendas with the given sheet
         *
         * @param sheet
         * @param {boolean} state
         */
        highlightMeetingsInCommon: function (sheet, state) {
            var meetings = this.findMeetings(sheet);

            for (var meetingIndex = 0; meetingIndex < meetings.length; meetingIndex++) {
                var sheetMet = this.findSheetAgendaBySheetId(meetings[meetingIndex].sheetMetId);

                if (null !== sheetMet) {
                    var meetingsSheetMet = this.findMeetings(sheetMet);

                    for (var meetingSheetMetIndex = 0; meetingSheetMetIndex < meetingsSheetMet.length; meetingSheetMetIndex++) {
                        if (meetingsSheetMet[meetingSheetMetIndex].meetingId === meetings[meetingIndex].meetingId) {
                            meetings[meetingIndex].highlight = state;
                            meetingsSheetMet[meetingSheetMetIndex].highlight = state;
                        }
                    }
                }
            }
        },

        /**
         * Load meeting data
         *
         * @param sheet
         * @param slot
         */
        loadMeetingUpdateSpot: function (sheet, slot) {
            if (slot.meetingId === undefined) {
                return;
            }

            this.isMeetingToUpdateLoading = true;

            this.$http.get(agendaApiEndpoints.getMeetingUpdateSpotEndpoint(slot.meetingId))
                .then(function(response) {
                    this.meetingToUpdate = {
                        sheet: sheet,
                        slot: slot,
                        form: response.data
                    };
                    this.isMeetingToUpdateLoading = false;
                }.bind(this))
                .catch(function(error) {
                    this.isMeetingToUpdateLoading = false;

                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                }.bind(this));
        },

        /**
         * Clear meetingToUpdate
         */
        clearMeetingToUpdate: function () {
            this.meetingToUpdate = null;
            this.isMeetingToUpdateLoading = false;
        },

        /**
         * Listener for "meeting-update" event
         */
        meetingUpdated: function () {
            if (null === this.meetingToUpdate) {
                return;
            }

            if (null !== this.meetingToUpdate.sheet) {
                this.loadAgenda(this.meetingToUpdate.sheet);
            }

            if (null !== this.meetingToUpdate.slot && null !== this.meetingToUpdate.slot.sheetMetId) {
                this.loadAgenda(this.findSheetBySheetId(this.meetingToUpdate.slot.sheetMetId));
            }
        },

        loadSlotsForMeeting: function (sheet, participant, slot) {
            this.isMeetingToUpdateLoading = true;
            this.meetingSlotToUpdate = {
                sheet: sheet,
                participant: participant,
                slot: slot
            };

            // query available slots for meeting slot.meetingId

            // mock
            this.availableSlotsForMeeting = [
                {
                    id: 29
                },
                {
                    id: 32
                },
                {
                    id: 38
                },
                {
                    id: 39
                }
            ];

            this.participantAvailableSlots(sheet, participant, true);
        },

        participantAvailableSlots: function (sheet, participant, enabled)
        {
            var participantAgenda = this.findParticipantAgenda(sheet, participant);

            for (var dayIndex = 0; dayIndex < participantAgenda.days.length; dayIndex++) {
                for (var slotIndex = 0; slotIndex < participantAgenda.days[dayIndex].slots.length; slotIndex++) {
                    var currentSlot = participantAgenda.days[dayIndex].slots[slotIndex];

                    for (var availableSlotIndex = 0; availableSlotIndex < this.availableSlotsForMeeting.length; availableSlotIndex++) {
                        if (this.availableSlotsForMeeting[availableSlotIndex].id === currentSlot.id) {
                            currentSlot.isAvailableForMeeting = enabled;
                            break;
                        }
                    }
                }
            }
        },

        updateMeetingSlot: function (newSlot) {
            this.isMeetingToUpdateLoading = false;

            if (null === this.meetingSlotToUpdate || null === this.meetingSlotToUpdate.sheet || null === this.meetingSlotToUpdate.slot) {
                return;
            }

            this.participantAvailableSlots(this.meetingSlotToUpdate.sheet, this.meetingSlotToUpdate.participant, false);
            this.loadAgenda(this.meetingSlotToUpdate.sheet);

            if (null !== this.meetingSlotToUpdate.slot && null !== this.meetingSlotToUpdate.slot.sheetMetId) {
                this.loadAgenda(this.findSheetBySheetId(this.meetingSlotToUpdate.slot.sheetMetId));
            }

            this.meetingSlotToUpdate = null;
        },

        isActive: function (slot) {
            return this.meetingSlotToUpdate !== null && slot.isAvailableForMeeting === true;
        }
    }
});
