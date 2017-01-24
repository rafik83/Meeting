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
        },
        isAvailableAction: function () {
            return this.isMeetingToUpdateLoading === false
                && this.meetingToUpdate === null
                && this.meetingSlotToUpdate === null;
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
                .then(function (response) {
                    this.sheets = response.data;
                }.bind(this))
                .catch(function (error) {
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
            if (-1 === this.findSheetAgenda(sheet)) {
                return;
            }

            this.cancelUpdateMeetingSlot();

            if (true === sheet.isAgendaLoading) {
                return;
            }

            sheet.isAgendaLoading = true;

            this.$http.get(agendaApiEndpoints.getSheetAgendaEndpoint(sheet))
                .then(function (response) {
                    var participants = response.data.participants;
                    var requests     = response.data.requests;
                    this.clearAgenda(sheet);
                    var sheetId = this.findSheetAgenda(sheet);

                    participants.forEach(function (participant) {
                        if (undefined !== this.agendas[sheetId] && undefined !== this.agendas[sheetId].participants) {
                            this.agendas[sheetId].participants.push(participant);
                        }
                    }.bind(this));

                    requests.forEach(function (request) {
                        request.participantsName = request.participants.map(function (participant) {
                            return participant.fullName;
                        }).join(', ');

                        if (undefined !== this.agendas[sheetId] && undefined !== this.agendas[sheetId].requests) {
                            this.agendas[sheetId].requests.push(request);
                        }
                    }.bind(this));

                    this.highlightMeetingsInCommon(sheet, true);
                    sheet.isAgendaLoading = false;

                    this.$forceUpdate();
                }.bind(this))
                .catch(function (error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }

                    sheet.isAgendaLoading = false;
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
            sheet.isAgendaLoading = false;
            this.cancelUpdateMeetingSlot();
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
            var sheetId = this.findSheetAgenda(sheet);

            if (-1 === sheetId) {
                return [];
            }

            if (undefined === this.agendas[sheetId]) {
                return [];
            }

            var participants = this.agendas[sheetId].participants;

            if (undefined === this.agendas[sheetId].participants) {
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
            var sheetId = this.findSheetAgenda(sheet);

            if (-1 === sheetId) {
                return null;
            }

            if (undefined === this.agendas[sheetId]) {
                return null;
            }

            if (undefined === this.agendas[sheetId].participants) {
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
                .then(function (response) {
                    this.meetingToUpdate = {
                        sheet: sheet,
                        slot: slot,
                        form: response.data
                    };
                    this.isMeetingToUpdateLoading = false;
                }.bind(this))
                .catch(function (error) {
                    this.isMeetingToUpdateLoading = false;
                    this.loadAgenda(sheet);

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

        /**
         * Load available slots for given meeting (slot) of participant and sheet
         *
         * @param sheet
         * @param participant
         * @param slot
         */
        loadSlotsForMeeting: function (sheet, participant, slot) {
            if (null == slot.meetingId) {
                return;
            }

            this.meetingSlotToUpdate = {
                sheet: sheet,
                participant: participant,
                slot: slot
            };

            this.$http.get(agendaApiEndpoints.getMeetingUpdateSlotEndpoint(slot.meetingId))
                .then(function (response) {
                    this.availableSlotsForMeeting = response.data.availableSlotsId;
                    this.changeSlotsStateAvailability(sheet, participant, true);
                }.bind(this))
                .catch(function (error) {
                    this.cancelUpdateMeetingSlot();
                    this.loadAgenda(sheet);

                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                }.bind(this));
        },

        /**
         * Change slots state of given participant and given sheet
         *
         * @param sheet
         * @param participant
         * @param {boolean} state
         */
        changeSlotsStateAvailability: function (sheet, participant, state)
        {
            var participantAgenda = this.findParticipantAgenda(sheet, participant);

            if (null === participantAgenda || null === participantAgenda.days) {
                return;
            }

            for (var dayIndex = 0; dayIndex < participantAgenda.days.length; dayIndex++) {
                for (var slotIndex = 0; slotIndex < participantAgenda.days[dayIndex].slots.length; slotIndex++) {
                    var currentSlot = participantAgenda.days[dayIndex].slots[slotIndex];

                    for (var availableSlotIndex = 0; availableSlotIndex < this.availableSlotsForMeeting.length; availableSlotIndex++) {
                        if (this.availableSlotsForMeeting[availableSlotIndex] === currentSlot.id) {
                            currentSlot.isAvailableForMeeting = state;
                            break;
                        }
                    }
                }
            }

            this.$forceUpdate();
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

            var meetingId = this.meetingSlotToUpdate.slot.meetingId;
            var sheetMetId = this.meetingSlotToUpdate.slot.sheetMetId;
            var sheet = this.meetingSlotToUpdate.sheet;
            this.cancelUpdateMeetingSlot();
            this.isMeetingToUpdateLoading = true;

            this.$http.post(agendaApiEndpoints.getMeetingUpdateSlotEndpoint(meetingId), {
                slotId: slot.id
            })
            .then(function () {
                this.loadAgenda(sheet);
                this.loadAgenda(this.findSheetBySheetId(sheetMetId));
            }.bind(this))
            .catch(function (error) {
                this.loadAgenda(sheet);
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
            return null !== this.meetingSlotToUpdate
                && null !== this.meetingSlotToUpdate.sheet
                && null !== this.meetingSlotToUpdate.slot
                && null !== this.meetingSlotToUpdate.slot.meetingId
                && null !== this.meetingSlotToUpdate.slot.sheetMetId;
        },

        /**
         * Clear available slots
         */
        clearAvailableSlots: function () {
            if (false === this.hasMeetingSlotToUpdate()) {
                return;
            }

            this.changeSlotsStateAvailability(this.meetingSlotToUpdate.sheet, this.meetingSlotToUpdate.participant, false);
        },

        /**
         * Cancel update meeting slot
         */
        cancelUpdateMeetingSlot: function () {
            this.isMeetingToUpdateLoading = false;
            this.clearAvailableSlots();
            this.meetingSlotToUpdate = null;
        },

        /**
         * Is given slot is available for meeting
         *
         * @param slot
         * @returns {boolean}
         */
        isAvailableForMeeting: function (slot) {
            return this.meetingSlotToUpdate !== null && slot.isAvailableForMeeting === true;
        }
    }
});
