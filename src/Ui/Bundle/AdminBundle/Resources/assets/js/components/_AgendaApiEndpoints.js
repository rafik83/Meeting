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
 * Returns /admin/fr/event/{event_id}/agenda/meeting/{meeting_id}/remove
 * or      /app_dev.php/admin/fr/event/{event_id}/agenda/meeting/{meeting_id}/remove
 *
 * @param {Object} slot
 * @returns {string}
 */
AgendaApiEndpoints.prototype.getRemoveMeetingEndpoint = function (slot) {
    return document.location.pathname + '/meeting/' + slot.meetingId + '/remove';
};

/**
 * Returns /admin/fr/event/{event_id}/agenda/meeting/{meetingId}/update-slot
 * or      /app_dev.php/admin/fr/event/{event_id}/agenda/meeting/{meetingId}/update-slot
 *
 * @param {int} meetingId
 * @returns {string}
 */
AgendaApiEndpoints.prototype.getMeetingUpdateSlotEndpoint = function (meetingId) {
    return this.getPathname() + '/meeting/' + meetingId + '/update-slot';
};

/**
 * Returns /admin/fr/event/{event_id}/agenda/request/{requestId}/transform-into-meeting
 * or      /app_dev.php/admin/fr/event/{event_id}/agenda/request/{requestId}/transform-into-meeting
 *
 * @param {int} requestId
 * @returns {string}
 */
AgendaApiEndpoints.prototype.getTransformRequestIntoMeetingEndpoint = function (requestId) {
    return this.getPathname() + '/request/' + requestId + '/transform-into-meeting';
};

module.exports = AgendaApiEndpoints;
