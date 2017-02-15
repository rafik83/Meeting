var options = require('../vueComponents/options'),
    DateTimePicker = require('../components/_DateTimePicker'),
    AgendaApiEndpoints = require('../components/_AgendaApiEndpoints'),
    moment = require('moment'),
    $ = require('jquery');

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
            boundedBegin: null,
            boundedEnd: null
        }
    },
    mounted: function () {
        var self = this;
        [].forEach.call(document.querySelectorAll('[data-datetimepicker]'), function (element) {
            new DateTimePicker(element);

            var inputType = element.dataset.datetimepicker;

            $(element).on('dp.change', function (event) {
                if (inputType === 'beginTime') {
                    self.beginTime = event.date.format('DD/MM/YYYY HH:mm');
                } else if (inputType === 'endTime') {
                    self.endTime = event.date.format('DD/MM/YYYY HH:mm');
                }
            });
        });
    },
    methods: {
        reset: function () {
            this.beginTime = null;
            this.endTime = null;
            this.enabled = false;
            this.boundedBegin = null;
            this.boundedEnd = null;
        },
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
                    this.boundedBegin = moment(response.data.massBegin.date).format('DD/MM/YYYY HH:mm');
                    this.boundedEnd = moment(response.data.massEnd.date).format('DD/MM/YYYY HH:mm');
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
