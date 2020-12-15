import updateParticipantForm from './../form/updateParticipantForm';
import options from '../../../vueComponents/options';
import AgendaApiEndpoints from "../../../components/_AgendaApiEndpoints";

var api = new AgendaApiEndpoints();

export default {
    template: '#update-participant-modal',
    delimiters: options.delimiters,
    props: ['show'],
    components: {
        'update-participant-form': updateParticipantForm
    },
    data: function () {
        return {
            meetingRequest: {},
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
        getParticipantIdOfParticipate: function(sheet) {
            return sheet.participants
                .filter(function (participant) {
                    return participant.participate;
                }).map(function (participant) {
                    return participant.id;
                });
        },
        save: function () {
            this.setUsedParticipants();

            this.$http.post(api.updateParticipantsOfRequestEndpoint(this.meetingRequest.requestId), {
                fromParticipants: this.getParticipantIdOfParticipate(this.data.fromSheet),
                toParticipants: this.getParticipantIdOfParticipate(this.data.toSheet)
            })
            .then(function (response) {
                response.data.forEach(function (sheetList) {
                    this.$emit('refresh-request-list-of-sheet', sheetList);
                }.bind(this));
            }.bind(this))
            .catch(function (error) {
                if (error.response) {
                    alert(error.response.data);
                } else {
                    alert(error.message);
                }
            }.bind(this));

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
        setRequest: function (meetingRequest) {
            Object.assign(this.meetingRequest, meetingRequest);
        },
        setUsedParticipants: function() {
            Object.assign(this.data, this.formData);
        }
    }
};
