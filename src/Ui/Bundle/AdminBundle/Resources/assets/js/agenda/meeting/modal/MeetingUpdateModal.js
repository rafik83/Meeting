import options from '../../../vueComponents/options';
import AgendaApiEndpoints from "../../../components/_AgendaApiEndpoints";

var api = new AgendaApiEndpoints();

export default {
    template: '#meeting-update-modal-template',
    delimiters: options.delimiters,
    props: {
        meetingToUpdate: {
            type: Object,
            default: function () {
                return {
                    form: {
                        meetingId: null,
                        blockedSlot: false,
                        blockedSpot: false,
                        spotId: null,
                        availableSpots: []
                    }
                }
            }
        }
    },
    data: function () {
        return {
            disabled: false
        }
    },
    methods: {
        reinit: function () {
            this.disabled = false;
        },
        close: function () {
            this.$emit('close-modal');
            this.reinit();
        },
        save: function () {
            this.disabled = true;
            this.$emit('meeting-updating');

            this.$http.post(api.getMeetingUpdateSpotEndpoint(this.meetingToUpdate.form.meetingId), {
                blockedSlot: this.meetingToUpdate.form.blockedSlot,
                blockedSpot: this.meetingToUpdate.form.blockedSpot,
                spotId: this.meetingToUpdate.form.spotId
            })
            .then(function () {
                this.$emit('meeting-updated');
                this.close();
            }.bind(this))
            .catch(function (error) {
                this.$emit('meeting-updated-error');
                if (error.response) {
                    alert(error.response.data);
                } else {
                    alert(error.message);
                }

                this.disabled = false;
            }.bind(this));
        }
    }
};
