var vue     = require('vue'),
    options = require('../vueComponents/options');

module.exports = {
    template: '#slot-agenda',
    delimiters: options.delimiters,
    props: ['slot-agenda', 'sheet', 'request'],
    methods: {
        select: function () {

        },
        isAvailableForMeeting: function () {

        },
        transformRequestIntoMeeting: function () {
            this.$http.post(agendaApiEndpoints.getTransformRequestIntoMeetingEndpoint(this.request), {
                slotId: this.slot-agenda.id
            }).then(function () {
                this.$emit('load-agenda', this.sheet);
            }.bind(this)).catch(function (error) {
                this.$emit('load-agenda', this.sheet);
                if (error.response) {
                    alert(error.response.data);
                } else {
                    alert(error.message);
                }
            }.bind(this));
        }
    }
};
