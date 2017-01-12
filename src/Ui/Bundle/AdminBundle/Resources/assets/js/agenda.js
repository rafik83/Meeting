var Vue   = require('vue'),
    axios = require('axios');

/**
 * Pass axios to Vue
 */
Vue.prototype.$http = axios;

new Vue({
    el: '#agenda',

    /**
     * Customs delimiters to avoid collision with Twig
     */
    delimiters: ['${', '}'],

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
        focus: null
    },

    /**
     * When Vue app is ready
     */
    mounted: function () {
        this.$nextTick(function () {
            this.init();
        });
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
            this.$http.get(this.getSheetsEndpoint())
                .then(function(response) {
                    this.sheets = response.data;
                }.bind(this))
                .catch(function(error) {
                    console.log(error);
                });
        },

        /**
         * Load sheet agenda data
         *
         * @param sheet
         */
        loadAgenda: function (sheet) {
            this.$http.get(this.getSheetAgendaEndpoint(sheet))
                .then(function(response) {
                    var participants                   = response.data.participants;
                    var requests                       = response.data.requests;
                    var sheetId                        = this.findSheetAgenda(sheet);
                    this.agendas[sheetId].participants = [];
                    this.agendas[sheetId].requests     = [];

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
                    console.log(error);
                });
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
            this.focusAgenda(sheet);
        },

        /**
         * Show agenda of given sheet id
         *
         * @param {int} sheetMetId
         */
        showAgendaForSheetId: function (sheetMetId) {
            var sheet = this.findSheetBySheetId(sheetMetId);

            if (null === sheet) {
                console.log('Sheet id = ' + sheetMetId + ' not found');
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
         * @param sheetId
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
         * Returns /admin/fr/event/{event_id}/agenda/sheets
         * or      /app_dev.php/admin/fr/event/{event_id}/agenda/sheets
         *
         * @returns {string}
         */
        getSheetsEndpoint: function () {
            return document.location.pathname + '/sheets';
        },

        /**
         * Returns /admin/fr/event/{event_id}/agenda/sheet/{sheet_id}
         * or      /app_dev.php/admin/fr/event/{event_id}/agenda/sheet/{sheet_id}
         *
         * @returns {string}
         */
        getSheetAgendaEndpoint: function (sheet) {
            return document.location.pathname + '/sheet/' + sheet.id;
        }
    }
});
