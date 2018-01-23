var axios = require('axios');

/**
 * @param {Element} slotAvailableElement
 * @param {int}     slotId
 */
function AvailableSheetsForSlot(slotAvailableElement, slotId) {
    axios.get(document.location.pathname + '/sheets-available-by-slot/' + slotId)
        .then(function (response) {
            if (parseInt(response.data.countAvailableSheets) > 0) {
                slotAvailableElement.querySelector('.content').innerHTML = response.data.message;
            }
        }).catch(function (error) {
            console.log(error);
        });
}

module.exports = AvailableSheetsForSlot;
