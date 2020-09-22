import options from '../../../vueComponents/options';
import DateTimePicker from "../../../../../../../../../../assets/js/components/DateTimePicker";
import AgendaApiEndpoints from "../../../components/_AgendaApiEndpoints";
import moment from 'moment';
import $ from 'jquery';

var api = new AgendaApiEndpoints();

import 'moment/locale/fr';
import 'moment/locale/en-gb';

export default {
    template: '#mass-assignment-form',
    delimiters: options.delimiters,
    props: [],
    data: function () {
        return {
            beginTime: null,
            endTime: null,
            enabled: false,
            boundedBegin: null,
            boundedEnd: null,
            enabledDate: null
        }
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
                    this.setEnabledDates(this.boundedBegin, this.boundedEnd);
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
         * @param {string} beginTime
         * @param {string} endTime
         */
        setEnabledDates: function(beginTime, endTime) {
            var self = this;

            [].forEach.call(document.querySelectorAll('[data-datetimepicker]'), function (element) {
                new DateTimePicker(element, {
                    'enabledDates': [
                        moment(beginTime, 'DD/MM/YYYY'),
                        moment(endTime, 'DD/MM/YYYY'),
                    ]
                });

                var inputType = element.dataset.datetimepicker;

                $(element).on('dp.change', function (event) {
                    if (inputType === 'beginTime') {
                        self.beginTime = event.date.format('DD/MM/YYYY HH:mm');
                    } else if (inputType === 'endTime') {
                        self.endTime = event.date.format('DD/MM/YYYY HH:mm');
                    }
                });
            });
        }
    }
};
