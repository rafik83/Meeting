import Meet from "./_Meet";

function AgendaAllSheet(element) {
    this.element = element;
    this.meetingModal = document.getElementById('meeting-modal');
    this.addMeet = this.addMeet.bind(this);
    Array.prototype.forEach.call(this.element.querySelectorAll('.meet'), this.addMeet);
}

AgendaAllSheet.prototype.addMeet = function (element) {
    new Meet(null, element, this.meetingModal);
};

export default AgendaAllSheet;
