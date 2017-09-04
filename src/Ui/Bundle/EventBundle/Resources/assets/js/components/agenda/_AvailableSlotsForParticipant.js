var axios        = require('axios'),
    EventEmitter = require('./_EventEmitter');

/**
 * @param {Element} element
 */
function AvailableSlotsForParticipant(element) {
    EventEmitter.call(this, element);

    this.availableSlotForParticipant = null;

    axios.get(document.location.pathname + '/slot/available')
        .then(function (response) {
            this.emitEvent(response.data);
        }.bind(this)).catch(function (error) {
            console.log(error);
        });
}

AvailableSlotsForParticipant.prototype = Object.create(EventEmitter.prototype);
AvailableSlotsForParticipant.prototype.constructor = AvailableSlotsForParticipant;

/**
 * Emit event to handle available slot on Agenda
 *
 * @param {Object} data
 */
AvailableSlotsForParticipant.prototype.emitEvent = function (data) {
    this.availableSlotForParticipant = data.availableSlotViews;
    this.emit('available-slot-handled');
};

module.exports = AvailableSlotsForParticipant;
