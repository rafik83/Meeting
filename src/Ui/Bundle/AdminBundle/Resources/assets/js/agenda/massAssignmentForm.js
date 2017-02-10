var options = require('../vueComponents/options'),
    DateTimePicker = require('../components/_DateTimePicker'),
    AgendaApiEndPoints = require('../components/_AgendaApiEndpoints');

var api = new AgendaApiEndPoints();

require('moment/locale/fr');
require('moment/locale/en-gb');

module.exports = {
    template: '#mass-assignment-form',
    delimiters: options.delimiters,
    props: ['agendaSlot'],
    data: function () {
        return {}
    },
    mounted: function () {
        [].forEach.call(document.querySelectorAll('[data-datatimepicker]'), function (element) {
            new DateTimePicker(element);
        });
    },
    methods: {
        init: function () {
            this.$http.get(api.getMassAssignmentDetailEndpoint(this.agendaSlot.id)
                .then(function (response) {

                }.bind(this))
                .catch(function (error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                }.bind(this)));
        }
    }
};
