var TypeCriteria                      = require('./_TypeCriteria'),
    HasMeetingToApproveCriteria       = require('./_HasMeetingToApproveCriteria'),
    HasNotEnoughAvailableSlotCriteria = require('./_HasNotEnoughAvailableSlotCriteria'),
    HasSentMeetingRequestCriteria     = require('./_HasSentMeetingRequestCriteria'),
    HasNotSentMeetingRequestCriteria  = require('./_HasNotSentMeetingRequestCriteria'),
    HasScheduledMeetingsCriteria      = require('./_HasScheduledMeetingsCriteria'),
    HasNoScheduledMeetingsCriteria    = require('./_HasNoScheduledMeetingsCriteria'),
    HasAvailableSlots                 = require('./_HasAvailableSlots'),
    HasValidatedRequestNotScheduled   = require('./_HasValidatedRequestNotScheduled');

/**
 * @param {array} filters
 * @constructor
 */
function SheetsFilter(filters)
{
    this.filters = filters;
}

/**
 * @param {array} sheets
 * @returns {array}
 */
SheetsFilter.prototype.filter = function (sheets)
{
    var typeCriteria = new TypeCriteria(this.filters.selectedTypes);
    var hasMeetingToApproveCriteria = new HasMeetingToApproveCriteria(this.filters.hasMeetingToApprove);
    var hasNotEnoughAvailableSlotCriteria = new HasNotEnoughAvailableSlotCriteria(this.filters.hasNotEnoughAvailableSlot);
    var hasSentMeetingRequestCriteria = new HasSentMeetingRequestCriteria(this.filters.hasSentMeetingRequest);
    var hasNotSentMeetingRequestCriteria = new HasNotSentMeetingRequestCriteria(this.filters.hasSentMeetingRequest);
    var hasScheduledMeetingsCriteria = new HasScheduledMeetingsCriteria(this.filters.hasScheduledMeetings);
    var hasNoScheduledMeetingsCriteria = new HasNoScheduledMeetingsCriteria(this.filters.hasScheduledMeetings);
    var hasAvailableSlotsCriteria = new HasAvailableSlots(this.filters.hasAvailableSlots);
    var hasValidatedRequestNotScheduled = new HasValidatedRequestNotScheduled(this.filters.hasValidatedRequestNotScheduled);

    sheets = typeCriteria.meetCriteria(sheets);
    sheets = hasMeetingToApproveCriteria.meetCriteria(sheets);
    sheets = hasNotEnoughAvailableSlotCriteria.meetCriteria(sheets);
    sheets = hasSentMeetingRequestCriteria.meetCriteria(sheets);
    sheets = hasNotSentMeetingRequestCriteria.meetCriteria(sheets);
    sheets = hasScheduledMeetingsCriteria.meetCriteria(sheets);
    sheets = hasNoScheduledMeetingsCriteria.meetCriteria(sheets);
    sheets = hasAvailableSlotsCriteria.meetCriteria(sheets);
    sheets = hasValidatedRequestNotScheduled.meetCriteria(sheets);

    return sheets;
};

module.exports = SheetsFilter;
