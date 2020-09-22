import TypeCriteria from "./_TypeCriteria";
import HasMeetingToApproveCriteria from "./_HasMeetingToApproveCriteria";
import HasNotEnoughAvailableSlotCriteria from "./_HasNotEnoughAvailableSlotCriteria";
import HasSentMeetingRequestCriteria from "./_HasSentMeetingRequestCriteria";
import HasNotSentMeetingRequestCriteria from "./_HasNotSentMeetingRequestCriteria";
import HasScheduledMeetingsCriteria from "./_HasScheduledMeetingsCriteria";
import HasNoScheduledMeetingsCriteria from "./_HasNoScheduledMeetingsCriteria";
import FollowerCriteria from "./_FollowerCriteria";
import NoFollowerCriteria from "./_NoFollowerCriteria";
import OrCriteria from "./_OrCriteria";
import ParticipantDataCriteria from "./_ParticipantDataCriteria";
import SheetTitleCriteria from "./_SheetTitleCriteria";
import HasAvailableSlots from "./_HasAvailableSlots";
import HasValidatedRequestNotScheduled from "./_HasValidatedRequestNotScheduled";
import HasParticipantUnavailableWithMeetingRequestCriteria from "./_HasParticipantUnavailableWithMeetingRequestCriteria";

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
    var followerCriteria = new FollowerCriteria(this.filters.selectedFollowers);
    var noFollowerCriteria = new NoFollowerCriteria(this.filters.selectedFollowers);
    var sheetTitleCriteria = new SheetTitleCriteria(this.filters.filterBySheetOrParticipantValue);
    var participantDataCriteria = new ParticipantDataCriteria(this.filters.filterBySheetOrParticipantValue);
    var hasAvailableSlotsCriteria = new HasAvailableSlots(this.filters.hasAvailableSlots);
    var hasValidatedRequestNotScheduled = new HasValidatedRequestNotScheduled(this.filters.hasValidatedRequestNotScheduled);
    var hasParticipantUnavailableWithMeetingRequestCriteria = new HasParticipantUnavailableWithMeetingRequestCriteria(this.filters.hasParticipantUnavailableWithMeetingRequest);

    sheets = typeCriteria.meetCriteria(sheets);
    sheets = hasMeetingToApproveCriteria.meetCriteria(sheets);
    sheets = hasNotEnoughAvailableSlotCriteria.meetCriteria(sheets);
    sheets = hasSentMeetingRequestCriteria.meetCriteria(sheets);
    sheets = hasNotSentMeetingRequestCriteria.meetCriteria(sheets);
    sheets = hasScheduledMeetingsCriteria.meetCriteria(sheets);
    sheets = hasNoScheduledMeetingsCriteria.meetCriteria(sheets);
    sheets = hasAvailableSlotsCriteria.meetCriteria(sheets);
    sheets = hasValidatedRequestNotScheduled.meetCriteria(sheets);
    sheets = hasParticipantUnavailableWithMeetingRequestCriteria.meetCriteria(sheets);

    sheets = (new OrCriteria(sheetTitleCriteria, participantDataCriteria)).meetCriteria(sheets);
    sheets = (new OrCriteria(followerCriteria, noFollowerCriteria)).meetCriteria(sheets);

    return sheets;
};

export default SheetsFilter;
