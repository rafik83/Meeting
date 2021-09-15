import $ from 'jquery';

function ParticipantPresence(element)
{
    this.element = element;
    this.endpoint = this.element.getAttribute('data-participant-presence-endpoint');

    // Check participant presence the first time
    this.getParticipantPresence();

    // Check participant presence every 30seconds
    setInterval(function() {
        this.getParticipantPresence();
    }.bind(this), 30000);
}

ParticipantPresence.prototype.getParticipantPresence = function () {
    var element = this.element;

    $.get(this.endpoint, function(response) {
        Object.values(response).map(function(participant) {
            var className = participant.present ? 'present': 'absent';
            element.querySelector('#participant-' + participant.id).setAttribute(
                'class',
                'badge badge-pill badge-' + className
            );
        });
    });
};

export default ParticipantPresence;
