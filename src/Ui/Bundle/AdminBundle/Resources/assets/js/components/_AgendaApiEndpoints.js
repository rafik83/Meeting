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


/**
 * Returns /admin/fr/event/{event_id}/agenda/sheet/{sheet_id}/meeting/{meeting_id}/remove
 * or      /app_dev.php/admin/fr/event/{event_id}/agenda/sheet/{sheet_id}/meeting/{meeting_id}/remove
 *
 * @returns {string}
 */
AgendaApiEndpoints.prototype.getRemoveMeetingEndpoint = function (sheet, slot) {
    return document.location.pathname + '/sheet/' + sheet.id + '/meeting/' + slot.meetingId + '/remove';
};


module.exports = AgendaApiEndpoints;
