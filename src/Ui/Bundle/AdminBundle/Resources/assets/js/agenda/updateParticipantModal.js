var updateParticipantForm = require('./updateParticipantForm'),
    options               = require('../vueComponents/options'),
    AgendaApiEndpoints    = require('./components/_AgendaApiEndpoints');

var agendaApiEndpoints = new AgendaApiEndpoints();

module.exports = {
    template: '#update-participant-modal',
    delimiters: options.delimiters,
    props: ['sheets', 'show'],
    components: {
        'update-participant-form': updateParticipantForm
    },
    data: function () {
        return {
            request: {},
            formData: {
                fromSheet: {},
                toSheet: {}
            },
            data: {
                fromSheet: {},
                toSheet: {}
            }
        }
    },
    methods: {
        refreshList: function () {
            this.$emit('refresh-list', this.sheet);
        },
        save: function () {
            this.setUsedFilter();

            agendaApiEndpoints.updateParticipantsEndpoint();
            this.$http.post(agendaApiEndpoints.getMeetingUpdateSpotEndpoint(this.meetingToUpdate.form.meetingId), {
                blockedSlot: this.meetingToUpdate.form.blockedSlot,
                blockedSpot: this.meetingToUpdate.form.blockedSpot,
                spotId: this.meetingToUpdate.form.spotId
            })
                .then(function (response) {
                    this.$emit('meeting-updated');
                    this.close();
                }.bind(this))
                .catch(function (error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }

                    this.disabled = false;
                }.bind(this));
            // // var sheet = this.sheet;
            //
            // this.sheet = sheet;
            // this.$emit('refresh-list', this.filteredSheets);
            this.$emit('close-modal');
        },
        reset: function () {
            this.data = {
                fromSheet: {},
                toSheet: {}
            };
            this.formData = {
                fromSheet: {},
                toSheet: {}
            }
        },
        setFormData: function (formData) {
            Object.assign(this.formData, formData);
        },
        setRequest: function (request) {
            Object.assign(this.request, request);
        }
    }
};
