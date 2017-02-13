var options = require('../vueComponents/options'),
    DateTimePicker = require('../components/_DateTimePicker'),
    AgendaApiEndpoints = require('../components/_AgendaApiEndpoints'),
    moment = require('moment');

var api = new AgendaApiEndpoints();

require('moment/locale/fr');
require('moment/locale/en-gb');

module.exports = {
    template: '#mass-assignment-form',
    delimiters: options.delimiters,
    props: [],
    data: function () {
        return {
            beginTime: null,
            endTime: null,
            enabled: false,
        }
    },
    mounted: function () {
        [].forEach.call(document.querySelectorAll('[data-datatimepicker]'), function (element) {
            new DateTimePicker(element);
        });
    },
    methods: {
        /**
         * Fetch Mass assignment details
         *
         * @param {int} massId
         */
        init: function (massId) {
            this.$http.get(api.getMassAssignmentDetailEndpoint(massId))
                .then(function (response) {
                    this.beginTime = moment(response.data.begin.date).format('DD/MM/YYYY HH:mm');
                    this.endTime = moment(response.data.end.date).format('DD/MM/YYYY HH:mm');
                    this.enabled = response.data.enabled;
                }.bind(this))
                .catch(function (error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                }.bind(this));
        },
    }
};
