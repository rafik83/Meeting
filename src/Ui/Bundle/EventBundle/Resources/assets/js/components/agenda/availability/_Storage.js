import axios from 'axios';

/**
 * Storage constructor
 *
 * @constructor
 */
function Storage(eventUnavailabilitiesCreateUrl, messages) {
    this.eventUnavailabilitiesCreateUrl = eventUnavailabilitiesCreateUrl;
    this.availaibilities = [];
    this.messages = messages;
}

Storage.prototype.addAvailability = function (availability) {
    this.availaibilities.push(availability);
};

Storage.prototype.save = function () {
    var payload = {payload: this.getData()};
    var this2 = this;

    axios
        .post(this.eventUnavailabilitiesCreateUrl, payload)
        .then(function (response) {
            if (typeof (response) != "object" || response == null
                || response.data === undefined || typeof (response.data) != "object" || response.data == null
                || response.data.success !== 'ok') {
                alert(this2.messages.onKo);
                return;
            }
            window.document.location.reload(true);
        })
        .catch(function () {
            alert(this2.messages.onFail);
        });
};

Storage.prototype.getData = function () {
    var result = [];

    [].forEach.call(this.availaibilities, function (availability) {
        var dayUnavailabilities = {
            day: {
                start: availability.agenda.startTimestamp,
                end: availability.agenda.endTimestamp
            },
            unavailabilities: []
        };
        [].forEach.call(availability.slots, function (slotToggle) {
            if (slotToggle.doesParticipantDeclareUnavailability() === false) {
                return;
            }
            var slot = slotToggle.slot;
            dayUnavailabilities.unavailabilities.push({
                begin: {
                    hour: slot.getHourFromTime(slot.time),
                    minute: slot.getMinutesFromTime(slot.time)
                },
                end: {
                    hour: slot.getHourFromTime(slot.time + slot.UIDuration),
                    minute: slot.getMinutesFromTime(slot.time + slot.UIDuration)
                }
            })
        });

        result.push(dayUnavailabilities);
    });

    return result;
};

export default Storage;
