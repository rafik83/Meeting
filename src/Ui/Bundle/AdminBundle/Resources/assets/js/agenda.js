var Vue                = require('vue'),
    axios              = require('axios'),
    filterModal        = require('./agenda/filterModal'),
    MeetingUpdateModal = require('./agenda/MeetingUpdateModal'),
    sheetAgenda        = require('./agenda/SheetAgenda'),
    slotAgenda         = require('./agenda/SlotAgenda'),
    options            = require('./vueComponents/options'),
    AgendaApiEndpoints = require('./components/_AgendaApiEndpoints');

var agendaApiEndpoints = new AgendaApiEndpoints();

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

new Vue({
    el: '#agenda',
    delimiters: options.delimiters,
    components: {
        'filter-modal': filterModal,
        'slot-agenda': slotAgenda,
        'sheet-agenda': sheetAgenda,
        'MeetingUpdateModal': MeetingUpdateModal
    },
    data: {
        sheets: [], /** {array} Sheet */
        openedSheets: [], /** {array} Opened sheet */
        focusedSheet: null, /** @param {Object} Sheet focused */
        isMeetingToUpdateLoading: false, /** Is meeting loading */
        meetingToUpdate: null, /** Meeting to update form */
        filteredSheets: [], /** Sheet[] */
        showFilterModal: false,
        hasUsedSheetFilter: false,
        meetingSlotToUpdate: null,
        availableSlotsForMeeting: [],
        meetingRequestToTransformIntoMeeting: null
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
        },
        sheetsIterator: function () {
            return this.hasUsedSheetFilter ? this.filteredSheets : this.sheets;
        },

        countFilteredSheets: function () {
            return this.sheetsIterator.length;
        }
    },

    methods: {
        /**
         * Init methods
         */
        init: function () {
            this.loadSheets();
        },

        showSheetFilter: function () {
            this.showFilterModal = true;
            var child = this.$refs.sheetFilterModal;
            if (typeof child !== 'undefined') {
                child.setFormFilter();
            }
        },

        /**
         * Event trigger when click on meeting update button in order to show update modal
         *
         * @param {Object} meetingToUpdate
         */
        showMeetingUpdateModal: function (meetingToUpdate) {
            this.meetingToUpdate = meetingToUpdate;
        },

        refreshList: function (filteredSheets) {
            this.hasUsedSheetFilter = true;
            this.filteredSheets = filteredSheets;
        },

        resetSheetFilter: function () {
            var child = this.$refs.sheetFilterModal;
            if (typeof child !== 'undefined') {
                child.reset();
                this.hasUsedSheetFilter = false;
                this.filteredSheets = [];
            }
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

        focusSheet: function (sheet) {
            this.focusedSheet = sheet
        },

        /**
         * Clear sheet agenda participants and requests data
         *
         * @param {Object} sheet
         */
        clearAgenda: function (sheet) {
            var sheetIndex = this.isOpenedSheet(sheet);

            if (-1 >= sheetIndex) {
                return;
            }

            this.openedSheets[sheetIndex].participants = [];
            this.openedSheets[sheetIndex].requests     = [];
        },

        /**
         * Load sheet agenda data
         *
         * @param {Object} sheet
         */
        loadAgenda: function (sheet) {
            sheet.isAgendaLoading = true;

            this.$http.get(agendaApiEndpoints.getSheetAgendaEndpoint(sheet))
                .then(function (response) {
                    var participants = response.data.participants;
                    var requests     = response.data.requests;

                    this.populateSheetAgenda(sheet, participants, requests);
                    this.focusedSheet = sheet;
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
         * Update agenda and requests of a given Sheet
         *
         * @param {Object} sheet
         * @param {array} participants
         * @param {array} requests
         */
        populateSheetAgenda: function(sheet, participants, requests) {
            this.clearAgenda(sheet);

            sheet.participants = participants;
            sheet.requests = [];

            requests.forEach(function (request) {
                request.participantsName = request.participants.map(function (participant) {
                    return participant.fullName;
                }).join(', ');

                sheet.requests.push(request);
            }.bind(this));

            // check if sheet already opened
            var openedSheetIndex = this.isOpenedSheet(sheet);

            if (openedSheetIndex === -1) {
                this.openedSheets.push(sheet); // add new opened sheet
            } else {
                this.$set(this.openedSheets[openedSheetIndex], 'participants', sheet.participants);
                this.$set(this.openedSheets[openedSheetIndex], 'requests', sheet.requests);

                var sheetAgendaFocused = this.findFocusedSheetComponent();

                if(sheetAgendaFocused !== undefined) {
                    sheetAgendaFocused.forceUpdate();
                }

                this.$forceUpdate();
            }

            this.highlightMeetingsInCommon(sheet, true);

            sheet.isAgendaLoading = false;
        },

        findFocusedSheetComponent: function () {
            var childs = this.$refs.childSheetAgenda;
            if (childs !== undefined) {
                for (var i = 0; i < childs.length; i++) {
                    if (childs[i].sheet.id === this.focusedSheet.id) {
                        return childs[i];
                    }
                }
            }

            return undefined;
        },

        /**
         * Show and focus agenda of given sheet
         *
         * @param {Object} sheet
         */
        showAndFocusAgenda: function (sheet) {
            this.showAgenda(sheet);
        },

        /**
         * Show agenda of given sheet
         *
         * @param sheet
         */
        showAgenda: function (sheet) {
            // check if sheet is already opened
            if (-1 !== this.isOpenedSheet(sheet)) {
                return;
            }

            // this.cancelSlotAction();
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
         * @param {Object} sheet
         *
         * @returns {Number}
         */
        findSheet: function (sheet) {
            return this.sheets.indexOf(sheet);
        },

        /**
         * Check if sheet is already open
         *
         * @param {Object} sheet
         *
         * @returns {Number}
         */
        isOpenedSheet: function (sheet) {
            return this.openedSheets.indexOf(sheet);
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
         * Find Sheet in opened openedSheets or returns null
         *
         * @param {int} sheetId
         * @returns null|sheet
         */
        findSheetAgendaBySheetId: function (sheetId) {
            for (var agendaIndex = 0; agendaIndex < this.openedSheets.length; agendaIndex++) {
                if (this.openedSheets[agendaIndex].id === sheetId) {
                    return this.openedSheets[agendaIndex];
                }
            }

            return null;
        },

        /**
         * Close given sheet agenda
         *
         * @param {Object} sheet
         */
        closeAgenda: function (sheet) {
            sheet.isAgendaLoading = false;
            this.cancelSlotAction();
            this.highlightMeetingsInCommon(sheet, false);
            this.openedSheets.splice(this.isOpenedSheet(sheet), 1);

            if (this.focusedSheet == sheet) {
                this.focusedSheet = null;
            }
        },

        /**
         * Find meetings for the given sheet
         *
         * @param sheet
         * @returns {Array} of meeting slots
         */
        findMeetings: function (sheet) {
            var sheetId = this.isOpenedSheet(sheet);

            if (-1 === sheetId) {
                return [];
            }

            if (undefined === this.openedSheets[sheetId]) {
                return [];
            }

            var participants = this.openedSheets[sheetId].participants;

            if (undefined === this.openedSheets[sheetId].participants) {
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
            var sheetId = this.isOpenedSheet(sheet);

            if (-1 === sheetId) {
                return null;
            }

            if (undefined === this.openedSheets[sheetId]) {
                return null;
            }

            if (undefined === this.openedSheets[sheetId].participants) {
                return null;
            }

            var index = this.openedSheets[sheetId].participants.indexOf(participant);

            if (-1 === index) {
                return null;
            }

            return this.openedSheets[sheetId].participants[index];
        },

        /**
         * Highlight meetings in common in opened openedSheets with the given sheet
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
         * Clear available slots
         */
        clearAvailableSlots: function () {
            for (var sheetIndex = 0; sheetIndex < this.openedSheets.length; sheetIndex++) {
                var participants = this.openedSheets[sheetIndex].participants;

                if (undefined === participants) {
                    continue;
                }

                for (var participantIndex = 0; participantIndex < participants.length; participantIndex++) {
                    for (var dayIndex = 0; dayIndex < participants[participantIndex].days.length; dayIndex++) {
                        for (var slotIndex = 0; slotIndex < participants[participantIndex].days[dayIndex].slots.length; slotIndex++) {
                            var slot                   = participants[participantIndex].days[dayIndex].slots[slotIndex];
                            slot.isAvailableForMeeting = false;
                        }
                    }
                }
            }

            this.availableSlotsForMeeting = [];
            this.$forceUpdate();
        },

        /**
         * Change slots state of given participant and given sheet
         *
         * @param sheet
         * @param participant
         */
        setSlotsStateAvailable: function (sheet, participant)
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
                            currentSlot.isAvailableForMeeting = true;
                            break;
                        }
                    }
                }
            }

            this.$forceUpdate();
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

            if (null !== this.meetingRequestToTransformIntoMeeting) {
                return this.transformRequestIntoMeeting(slot);
            }

            this.cancelSlotAction();
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
            this.cancelSlotAction();

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
         * Transform request into meeting
         *
         * @param slot
         */
        transformRequestIntoMeeting: function (slot) {
            var sheet = this.focusedSheet;
            var requestId = this.meetingRequestToTransformIntoMeeting.requestId;
            var sheetMetId = this.meetingRequestToTransformIntoMeeting.sheetMetId;
            this.cancelSlotAction();

            this.$http.post(agendaApiEndpoints.getTransformRequestIntoMeetingEndpoint(requestId), {
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
         * Cancel update meeting slot
         */
        cancelSlotAction: function () {
            this.clearAvailableSlots();
            this.isMeetingToUpdateLoading = false;
            this.meetingSlotToUpdate = null;
            this.meetingRequestToTransformIntoMeeting = null;
        },

        /**
         * Is given slot is available for meeting
         *
         * @param slot
         * @returns {boolean}
         */
        isAvailableForMeeting: function (slot) {
            return slot.isAvailableForMeeting === true
                && (null !== this.meetingSlotToUpdate || null !== this.meetingRequestToTransformIntoMeeting);
        },

        /**
         * Load slots available for given meetingRequest
         *
         * @param {Object} meetingRequest
         */
        loadSlotsForRequest: function (meetingRequest) {
            this.cancelSlotAction();
            this.meetingRequestToTransformIntoMeeting = meetingRequest;

            this.$http.get(agendaApiEndpoints.getTransformRequestIntoMeetingEndpoint(this.meetingRequestToTransformIntoMeeting.requestId))
                .then(function (response) {
                    // check if this actions is still live
                    if (null !== this.meetingRequestToTransformIntoMeeting) {
                        this.availableSlotsForMeeting = response.data.availableSlotsId;

                        this.showAvailableSlotsForRequest();
                    }
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
         * Show available slots for request
         */
        showAvailableSlotsForRequest: function () {
            var sheet = this.focusedSheet;

            if (null === this.meetingRequestToTransformIntoMeeting || null === sheet) {
                return;
            }

            var requestParticipants = this.meetingRequestToTransformIntoMeeting.participants;

            for (var index = 0; index < requestParticipants.length; index++) {
                var participant = this.findParticipantById(sheet, requestParticipants[index].id);
                this.setSlotsStateAvailable(sheet, participant);
            }
        },

        /**
         * Find participant agenda by participantId
         *
         * @param sheet
         * @param participantId
         * @returns null|participant
         */
        findParticipantById: function (sheet, participantId) {
            var sheetId = this.isOpenedSheet(sheet);

            if (-1 === sheetId) {
                return null;
            }

            if (undefined === this.openedSheets[sheetId]) {
                return null;
            }

            if (undefined === this.openedSheets[sheetId].participants) {
                return null;
            }

            var participants = this.openedSheets[sheetId].participants;

            for (var index = 0; index < participants.length; index++) {
                if (participants[index].id === participantId) {
                    return participants[index];
                }
            }

            return null;
        }
    }
});
