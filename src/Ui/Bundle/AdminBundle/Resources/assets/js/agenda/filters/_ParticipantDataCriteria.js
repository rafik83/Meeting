import Criteria from "./_Criteria";

/**
 * @param {string} participantData
 * @constructor
 */
function ParticipantDataCriteria(participantData) {
    this.participantData = participantData;
}

ParticipantDataCriteria.prototype = new Criteria();

/**
 * @param {array} sheets
 * @returns {array}
 */
ParticipantDataCriteria.prototype.meetCriteria = function(sheets) {

    if (typeof this.participantData !== 'undefined') {
        return sheets.filter(function (sheet) {
            var filteredParticipants = sheet.participants.filter(function (participant) {
                var fullnameMatch = participant.fullname.search(new RegExp(this.participantData, 'i')) !== -1;
                var emailMatch    = participant.email.search(new RegExp(this.participantData, 'i')) !== -1;

                return true === fullnameMatch || true === emailMatch;
            }.bind(this));

            return filteredParticipants.length > 0;
        }.bind(this));
    }

    return sheets;
};

export default ParticipantDataCriteria;
