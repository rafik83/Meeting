var options = require('../vueComponents/options'),
    massAssignmentForm = require('./massAssignmentForm'),
    AgendaApiEndPoints = require('../components/_AgendaApiEndpoints');

var api = new AgendaApiEndPoints();

module.exports = {
    template: '#mass-assignment-modal-template',
    delimiters: options.delimiters,
    props: ['show'],
    components: { 'mass-assignment-form': massAssignmentForm },
    data: function () {
        return  {}
    },
    methods: {
        /**
         * Fetch Mass assignment details
         *
         * @param {int} massId
         */
        init: function(massId) {
            this.$http.get(api.getMassAssignmentDetailEndpoint(massId))
                .then(function (response) {
                    console.log(response);
                }.bind(this))
                .catch(function (error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                }.bind(this));
        },
        save: function () {
            
        }
    }
};
