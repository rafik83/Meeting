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
    computed: {
        possibleSlots: function () {
            if (this.meetingToUpdate.form.meetingParticipants.length === 0) {
                return [];
            }

            let possibleSlotsIdForParticipants = this.meetingToUpdate.form.currentSheetAvailableSlotIds[this.meetingToUpdate.form.meetingParticipants[0]];


            if (!possibleSlotsIdForParticipants || possibleSlotsIdForParticipants.length === 0) {
                return [];
            }

            const otherSelectedParticipants = this.meetingToUpdate.form.meetingParticipants.slice(1);
            const currentSheetAvailableSlotIds = this.meetingToUpdate.form.currentSheetAvailableSlotIds;
            otherSelectedParticipants.forEach(function (participantId) {
                possibleSlotsIdForParticipants = possibleSlotsIdForParticipants.filter(slotId => currentSheetAvailableSlotIds[participantId].includes(slotId));
            });

            return this.meetingToUpdate.form.meetingSlots.filter(slot => possibleSlotsIdForParticipants.includes(slot.id));
        },
        possibleSpots: function() {
            // filter by seat capacity
            let possibleSpotsIdForSlots = this.meetingToUpdate.form.availableSpots.filter(spot => spot.seatCapacity >= this.partipantsCount);

            // filter by selected slot
            return possibleSpotsIdForSlots.filter(spot => spot.slotsId.includes(this.meetingToUpdate.form.slotId));
        },
        partipantsCount: function() {
           return this.meetingToUpdate.form.metParticipantsCount + this.meetingToUpdate.form.meetingParticipants.length;
        },
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

            this.$http.post(api.getMeetingUpdateSpotEndpoint(this.meetingToUpdate.form.meetingId, this.meetingToUpdate.sheet.id), {
                blockedSlot: this.meetingToUpdate.form.blockedSlot,
                blockedSpot: this.meetingToUpdate.form.blockedSpot,
                spotId: this.meetingToUpdate.form.spotId,
                slotId: this.meetingToUpdate.form.slotId,
                meetingParticipants: this.meetingToUpdate.form.meetingParticipants
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
