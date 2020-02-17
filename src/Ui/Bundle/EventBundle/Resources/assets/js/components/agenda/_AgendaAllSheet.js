var Meet = require('./_Meet');

function AgendaAllSheet(element) {
    this.element = element;
    this.moveMeetingModal = document.getElementById('meeting-modal');
    Array.prototype.forEach.call(this.element.querySelectorAll('.meet'), this.addMeet);
}

AgendaAllSheet.prototype.addMeet = function (element) {
    new Meet(null, element, this.moveMeetingModal);
};

module.exports = AgendaAllSheet;
