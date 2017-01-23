function AgendaApiEndpoints() {}

AgendaApiEndpoints.prototype.getPathname = function ()
{
    return document.location.pathname;
};

/**
 * Returns /admin/fr/event/{event_id}/agenda/sheets
 * or      /app_dev.php/admin/fr/event/{event_id}/agenda/sheets
 *
 * @returns {string}
 */
AgendaApiEndpoints.prototype.getSheetsEndpoint = function ()
{
    return this.getPathname() + '/sheets';
};

/**
 * Returns /admin/fr/event/{event_id}/agenda/sheet/{sheet_id}
 * or      /app_dev.php/admin/fr/event/{event_id}/agenda/sheet/{sheet_id}
 *
 * @param {int} sheet
 * @returns {string}
 */
AgendaApiEndpoints.prototype.getSheetAgendaEndpoint = function (sheet)
{
    return this.getPathname() + '/sheet/' + sheet.id;
};

/**
 * Returns /admin/fr/event/{event_id}/agenda/meeting/{meetingId}/update-spot
 * or      /app_dev.php/admin/fr/event/{event_id}/agenda/meeting/{meetingId}/update-spot
 *
 * @param {int} meetingId
 * @returns {string}
 */
AgendaApiEndpoints.prototype.getMeetingUpdateSpotEndpoint = function (meetingId) {
    return this.getPathname() + '/meeting/' + meetingId + '/update-spot';
};

module.exports = AgendaApiEndpoints;
