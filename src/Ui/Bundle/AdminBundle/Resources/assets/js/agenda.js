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
                    var sheetId                        = this.findSheetAgenda(sheet);
                    this.agendas[sheetId].participants = [];

                    participants.forEach(function (participant) {
                        this.agendas[sheetId].participants.push(participant);
                    }.bind(this));

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

            this.loadAgenda(sheet);
            this.focusAgenda(sheet);
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
         * Close given sheet agenda
         *
         * @param sheet
         */
        closeAgenda: function (sheet) {
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
