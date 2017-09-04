var axios = require('axios');

/**
 * @param {Slot} slot
 * @param {int} sheetId
 */
function AvailableSheetsForSlot(slot, sheetId) {
console.log(slot);
    axios.get(document.location.pathname + '/sheet/' + sheetId + '/agenda/slot/' + slot.id )
        .then(function (response) {
            console.log(response);
        }).catch(function (error) {
            console.log(error);
        }
    );
}

module.exports = AvailableSheetsForSlot;
