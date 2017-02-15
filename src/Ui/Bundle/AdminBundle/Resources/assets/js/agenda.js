var Vue                = require('vue'),
    axios              = require('axios'),
    filterModal        = require('./agenda/filterModal'),
    meetingUpdateModal = require('./agenda/MeetingUpdateModal'),
    updateParticipantModal = require('./agenda/updateParticipantModal'),
    sheetAgenda        = require('./agenda/SheetAgenda'),
    slotAgenda         = require('./agenda/SlotAgenda'),
    sortModal          = require('./agenda/sortModal'),
    options            = require('./vueComponents/options'),
    AgendaApiEndpoints = require('./components/_AgendaApiEndpoints');

var api = new AgendaApiEndpoints();

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
        'update-participant-modal': updateParticipantModal,
        'slot-agenda': slotAgenda,
        'sheet-agenda': sheetAgenda,
        'MeetingUpdateModal': meetingUpdateModal,
        'sort-modal': sortModal
    },
    data: {
        sheets: [], /** {array} Sheet */
        openedSheets: [], /** {array} Opened sheet */
        focusedSheet: null, /** @param {Object} Sheet focused */
        isMeetingToUpdateLoading: false, /** Is meeting loading */
        meetingToUpdate: null, /** Meeting to update form */
        filteredSheets: [], /** Sheet[] */
        showFilterModal: false,
        showParticipantModal: false,
        hasUsedSheetFilter: false,
        showSortModal: false,

        /**
         * Meeting slot to update
         */
        meetingSlotToUpdate: null,
        meetingRequestToTransformIntoMeeting: null /** @param {Object} Meeting request **/
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

        /**
         * Filters - Show the filter Modal
         */
        showSheetFilter: function () {
            this.showFilterModal = true;
            var child = this.$refs.sheetFilterModal;
            if (typeof child !== 'undefined') {
                child.setFormFilter();
            }
        },

        /**
         * Show the participants of the meeting request to update them
         *
         * @param {Object} meetingRequest
         */
        showParticipants: function (meetingRequest) {
            this.$http.get(api.getParticipantsOfRequestEndpoint(meetingRequest.requestId))
                .then(function (response) {
                    var participantModalComponent = this.$refs.updateParticipantModal;

                    if (typeof participantModalComponent !== 'undefined') {
                        participantModalComponent.setFormData(response.data);
                        participantModalComponent.setRequest(meetingRequest);
                    }

                    this.showParticipantModal = true;

                }.bind(this))
                .catch(function (error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                });
        },

        showSort: function () {
            this.showSortModal = true;
        },

        /**
         * Filters - Reset filteredSheet
         */
        resetSheetFilter: function () {
            var child = this.$refs.sheetFilterModal;
            if (typeof child !== 'undefined') {
                child.reset();
                this.hasUsedSheetFilter = false;
                this.filteredSheets = [];
            }
        },

        /**
         * Filters - refresh filteredSheets
         *
         * @param {array} filteredSheets
         */
        refreshList: function (filteredSheets) {
            this.hasUsedSheetFilter = true;
            this.filteredSheets = filteredSheets;
        },

        /**
         * Event trigger when click on meeting update button in order to show update modal
         *
         * @param {Object} meetingToUpdate
         */
        showMeetingUpdateModal: function (meetingToUpdate) {
            this.meetingToUpdate = meetingToUpdate;
        },

        /**
         * Load sheets data
         */
        loadSheets: function () {
            this.$http.get(api.getSheetsEndpoint())
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
            // Cancel action when the focus is changed
            this.clearMeetingToUpdate();
            this.cancelSlotAction();

            this.focusedSheet = sheet;
        },

        /**
         * Show and focus agenda of given sheet
         *
         * @param {Object} sheet
         */
        showAndFocusAgenda: function (sheet) {
            this.cancelSlotAction();
            this.showAgenda(sheet);
            this.focusedSheet = sheet;
        },

        /**
         * Show agenda of given sheet
         *
         * @param {Object} sheet
         */
        showAgenda: function (sheet) {
            // check if sheet is already opened
            if (this.isOpenedSheet(sheet)) {
                return;
            }

            this.highlightMeetingsInCommon(sheet, true);
            this.loadAgenda(sheet, false);
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

            this.focusedSheet = sheet;
            this.showAgenda(sheet);
        },

        /**
         * Load sheet agenda data
         *
         * @param {Object} sheet
         * @param {boolean} givenForce = false
         */
        loadAgenda: function (sheet, givenForce) {
            var force = givenForce || false;

            // prevent execute api request twice if sheet agenda already loaded
            if (force === false && (this.isOpenedSheet(sheet) || sheet.isAgendaLoading === true)) {
                return;
            }

            sheet.isAgendaLoading = true;

            this.$http.get(api.getSheetAgendaEndpoint(sheet))
                .then(function (response) {
                    var participants = response.data.participants;
                    var requests     = response.data.requests;

                    this.populateSheetAgenda(sheet, participants, requests);
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
         * Event handler for refresh-agenda
         *
         * @param {Object} sheet
         */
        refreshAgenda: function (sheet) {
            this.loadAgenda(sheet, true);
        },

        /**
         * Event handler for refresh-both-agenda
         *
         * @param {Object} event
         */
        handleRefreshBothAgenda: function(event) {
            var sheetMet = this.findSheetBySheetId(event.sheetMetId);
            this.loadAgenda(event.sheet, true);

            // reload sheet met agenda if opened
            if (sheetMet !== null && this.isOpenedSheet(sheetMet)) {
                this.loadAgenda(sheetMet, true);
            }
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
            this.populateRequestList(sheet, requests);

            // check if sheet already opened
            var openedSheetIndex = this.getOpenedSheetIndex(sheet);

            if (openedSheetIndex === -1) {
                this.openedSheets.push(sheet); // add new opened sheet
            } else {
                this.$set(this.openedSheets[openedSheetIndex], 'participants', sheet.participants);
                this.$set(this.openedSheets[openedSheetIndex], 'requests', sheet.requests);

                this.forceUpdateSheet(sheet);
                this.$forceUpdate();
            }

            this.highlightMeetingsInCommon(sheet, true);

            var focusedComponent = this.findFocusedSheetComponent();
            if (focusedComponent !== undefined) {
                focusedComponent.setSlotActionButtonsState(true);
            }

            sheet.isAgendaLoading = false;
        },

        populateRequestList: function (sheet, requests) {
            sheet.requests = [];

            requests.forEach(function (request) {
                request.participantsName = request.participants.map(function (participant) {
                    return participant.fullName;
                }).join(', ');

                sheet.requests.push(request);
            }.bind(this));
        },

        /**
         * Clear sheet agenda participants and requests data
         *
         * @param {Object} sheet
         */
        clearAgenda: function (sheet) {
            var sheetIndex = this.getOpenedSheetIndex(sheet);

            if (-1 >= sheetIndex) {
                return;
            }

            this.openedSheets[sheetIndex].participants = [];
            this.openedSheets[sheetIndex].requests     = [];
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
            this.openedSheets.splice(this.getOpenedSheetIndex(sheet), 1);

            if (this.focusedSheet == sheet) {
                this.focusedSheet = this.focusOnLastSheetOrNull();
            }
        },

        /**
         * Refresh the requests list of the open Sheet
         *
         * @param {Object} event (object composed of sheetId and array of Request)
         */
        refreshRequestListOfSheet: function(event) {
            if (typeof event.sheetId !== 'undefined' && typeof event.requests !== 'undefined') {
                var openSheet = this.findOpenedSheetById(event.sheetId);

                if (openSheet !== null) {
                    this.populateRequestList(openSheet, event.requests);

                    if (this.focusedSheet === openSheet) {
                        this.$forceUpdate();
                    }
                }

                this.cancelSlotAction();
            }
        },

        /**
         * Loop on <SheetAgenda> component instances and return the one associated
         * to this Sheet
         *
         * @returns {Object}|null
         */
        findFocusedSheetComponent: function () {
            var childs = this.$refs.childSheetAgenda;

            if (childs !== undefined) {
                for (var i = 0; i < childs.length; i++) {
                    if (childs[i].sheet.id === this.focusedSheet.id) {
                        return childs[i];
                    }
                }
            }

            return null;
        },

        /**
         * @param {Object} sheet
         * @returns {Object}|null
         */
        findSheetComponent: function (sheet) {
            var childs = this.$refs.childSheetAgenda;

            if (childs !== undefined) {
                for (var i = 0; i < childs.length; i++) {
                    if (childs[i].sheet.id === sheet.id) {
                        return childs[i];
                    }
                }
            }

            return null;
        },

        /**
         * Check if sheet is already open
         *
         * @param {Object} sheet
         *
         * @returns {boolean}
         */
        isOpenedSheet: function (sheet) {
            return -1 !== this.openedSheets.indexOf(sheet);
        },

        /**
         * get sheet index if it is open
         *
         * @param {Object} sheet
         *
         * @returns {Number}
         */
        getOpenedSheetIndex: function (sheet) {
            return this.openedSheets.indexOf(sheet);
        },

        /**
         * Find Sheet or returns null
         *
         * @param {int} sheetId
         *
         * @returns null|{Object} sheet
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
         *
         * @returns null|{Object} sheet
         */
        findOpenedSheetById: function (sheetId) {
            for (var agendaIndex = 0; agendaIndex < this.openedSheets.length; agendaIndex++) {
                if (this.openedSheets[agendaIndex].id === sheetId) {
                    return this.openedSheets[agendaIndex];
                }
            }

            return null;
        },

        /**
         * Get the first opened sheet
         *
         * @returns {Object|null}
         */
        focusOnLastSheetOrNull: function() {
            for (var agendaIndex = 0; agendaIndex < this.openedSheets.length; agendaIndex++) {
                if (this.openedSheets[agendaIndex].id !== null) {
                    return this.focusedSheet = this.openedSheets[agendaIndex];
                }
            }
            return null;
        },

        /**
         * Find meetings for the given sheet
         *
         * @param {Object} sheet
         * @returns {Array} of meeting slots
         */
        findMeetings: function (sheet) {
            var sheetId = this.getOpenedSheetIndex(sheet);

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
         * Highlight meetings in common in opened openedSheets with the given sheet
         *
         * @param sheet
         * @param {boolean} state
         */
        highlightMeetingsInCommon: function (sheet, state) {
            var meetings = this.findMeetings(sheet);

            for (var meetingIndex = 0; meetingIndex < meetings.length; meetingIndex++) {
                var sheetMet = this.findOpenedSheetById(meetings[meetingIndex].sheetMetId);

                if (null !== sheetMet) {
                    var meetingsSheetMet = this.findMeetings(sheetMet);

                    for (var meetingSheetMetIndex = 0; meetingSheetMetIndex < meetingsSheetMet.length; meetingSheetMetIndex++) {
                        if (meetingsSheetMet[meetingSheetMetIndex].meetingId === meetings[meetingIndex].meetingId) {
                            meetings[meetingIndex].highlight = state;
                            meetingsSheetMet[meetingSheetMetIndex].highlight = state;
                        }
                    }

                    this.forceUpdateSheet(sheetMet);
                }
            }
        },

        /**
         * This method force update the sheetComponent of the given Sheet
         *
         * @param {Object} sheet
         */
        forceUpdateSheet: function (sheet) {
            var sheetMetComponent = this.findSheetComponent(sheet)

            if (sheetMetComponent !== null) {
                sheetMetComponent.forceUpdate();
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
         * Listener for "meeting-updated" event
         */
        meetingUpdated: function () {
            if (null === this.meetingToUpdate) {
                return;
            }

            if (null !== this.meetingToUpdate.sheet) {
                this.loadAgenda(this.meetingToUpdate.sheet, true);
            }

            if (null !== this.meetingToUpdate.slot && null !== this.meetingToUpdate.slot.sheetMetId) {
                this.loadAgenda(this.findSheetBySheetId(this.meetingToUpdate.slot.sheetMetId), true);
            }
        },

        /**
         * Listener for "meeting-updated-error" event
         */
        handleMeetingUpdatedError: function () {
            this.meetingUpdated();
        },

        /**
         * Listener for "meeting-updating" event
         */
        handleMeetingUpdating: function () {
            if (null === this.meetingToUpdate) {
                return;
            }

            if (this.meetingToUpdate.slot !== null) {
                var slot = this.meetingToUpdate.slot;
                slot.isActionButtonsEnabled = false;

                this.forceUpdateSheet(this.meetingToUpdate.sheet);

                var sheetMet = this.findOpenedSheetById(slot.sheetMetId);

                if (sheetMet !== null) {
                    var meetingsSheetMet = this.findMeetings(sheetMet);

                    for (var meetingSheetMetIndex = 0; meetingSheetMetIndex < meetingsSheetMet.length; meetingSheetMetIndex++) {
                        if (meetingsSheetMet[meetingSheetMetIndex].meetingId === slot.meetingId) {
                            meetingsSheetMet[meetingSheetMetIndex].isActionButtonsEnabled = false;
                        }
                    }

                    this.forceUpdateSheet(sheetMet);
                }
            }
        },

        /**
         * Transform Meeting Request into Meeting and refresh sheet's agenda
         *
         * @param {Object} slot
         */
        transformRequestIntoMeeting: function (slot) {
            var sheet      = this.focusedSheet;
            var requestId  = this.meetingRequestToTransformIntoMeeting.requestId;
            var sheetMetId = this.meetingRequestToTransformIntoMeeting.sheetMetId;
            var sheetMet   = this.findOpenedSheetById(sheetMetId);


            this.$http.post(api.getTransformRequestIntoMeetingEndpoint(requestId), {
                slotId: slot.id
            })
            .then(function () {
                this.loadAgenda(sheet, true); // reload focused sheet agenda
                if (sheetMet !== null) {
                    this.loadAgenda(sheetMet, true); // reload sheet met agenda
                }

                this.meetingRequestToTransformIntoMeeting = null;
            }.bind(this))
            .catch(function (error) {
                this.loadAgenda(sheet, true);
                if (error.response) {
                    alert(error.response.data);
                } else {
                    alert(error.message);
                }
            }.bind(this));
        },
        
        /**
         * Cancel update meeting slot
         */
        cancelSlotAction: function () {
            var focusedSheetComponent = this.findFocusedSheetComponent();

            if (focusedSheetComponent === null) {
                return;
            }

            focusedSheetComponent.clearAvailableSlots();

            this.isMeetingToUpdateLoading = false;
            this.meetingSlotToUpdate = null;
            this.meetingRequestToTransformIntoMeeting = null;
        },

        /**
         * Load slots available for given meetingRequest
         *
         * @param {Object} meetingRequest
         */
        loadSlotsForRequest: function (meetingRequest) {
            this.cancelSlotAction();

            var focusedComponent = this.findFocusedSheetComponent();

            if (focusedComponent === null) {
                return false;
            }

            this.meetingRequestToTransformIntoMeeting = meetingRequest;

            this.$http.get(api.getTransformRequestIntoMeetingEndpoint(this.meetingRequestToTransformIntoMeeting.requestId))
                .then(function (response) {
                    // check if this actions is still live
                    if (null !== this.meetingRequestToTransformIntoMeeting) {
                        focusedComponent.showAvailableSlotsForRequest(
                            meetingRequest,
                            response.data.availableSlotsId
                        );
                    }
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
});
