var vue = require('vue');

module.exports = {
    template: '#slot',
    props: ['slot', 'sheet', 'request'],
    methods: {
        select: function () {

        },
        isAvailableForMeeting: function () {

        },
        transformRequestIntoMeeting: function () {
            this.$http.post(agendaApiEndpoints.getTransformRequestIntoMeetingEndpoint(this.request), {
                slotId: this.slot.id
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
