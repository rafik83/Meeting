<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event;

final class Events
{
    const SHEET_VALIDATED                              = 'sheet.validated';
    const SHEET_ACCEPTED                               = 'sheet.accepted';
    const SHEET_PENDING                                = 'sheet.pending';
    const SHEET_INVITATION_CLOSE_TO_EXPIRATION         = 'sheet.invitation.close_to_expiration';
    const SHEET_ADD_PARTICIPANT_CONFIRMATION           = 'sheet.participant.add.confirmation';
    const SHEET_CHANGED_TYPE                           = 'sheet.changed_type';
    const SHEET_VALIDATION_DRAFT                       = 'sheet.validation.draft';
    const SHEET_VALIDATION_PENDING                     = 'sheet.validation.pending';
    const SHEET_VALIDATION_VALIDATE                    = 'sheet.validation.validate';
    const SHEET_GROUP_CREATED                          = 'sheet.group.created';
    const SHEET_GROUP_UPDATED                          = 'sheet.group.updated';
    const SHEET_COMPLETENESS                           = 'sheet.completeness';
    const SHEET_ENABLE_DISABLE                         = 'sheet.enable_disable';
    const SHEET_CATALOG                                = 'sheet.catalog';
    const SHEET_INVOICED                               = 'sheet.invoiced';
    const SHEET_UPDATED                                = 'sheet.updated';
    const SHEET_TITLE_CHECK                            = 'sheet.title';
    const SHEET_CREATE_BY_GROUP_MANAGER                = 'group.sheet.created';
    const USER_MAIL_CHANGED                            = 'change_mail';
    const MEETING_PARTICIPANT_ADDED                    = 'meeting.participant.added';
    const MEETING_PARTICIPANT_REMOVED                  = 'meeting.participant.removed';
    const MEETING_PARTICIPATE                          = 'meeting.participate';
    const MEETING_UN_PARTICIPATE                       = 'meeting.un_participate';
    const MEETING_REQUEST_CREATED                      = 'meeting_request.created';
    const MEETING_REQUEST_CANCELED                     = 'meeting_request.canceled';
    const MEETING_REQUEST_REFUSED                      = 'meeting_request.refused';
    const MEETING_REQUEST_APPROVED                     = 'meeting_request.approved';
    const MEETING_REQUEST_UNAPPROVED                   = 'meeting_request.unapproved';
    const MEETING_REQUEST_UNREFUSED                    = 'meeting_request.unrefused';
    const MEETING_CANCELED                             = 'meeting.canceled';
    const MEETING_REMOVED                              = 'meeting.removed';
    const MEETING_CREATED                              = 'meeting.created';
    const MEETING_MOVED                                = 'meeting.moved';
    const REQUEST_PARTICIPANT_ADDED                    = 'meeting_request.participant.added';
    const REQUEST_PARTICIPANT_REMOVED                  = 'meeting_request.participant.removed';
    const REQUEST_UPDATE_MESSAGE                       = 'meeting_request.update.message';
    const MEETING_UPDATE_MESSAGE                       = 'meeting.update.message';
    const EVENT_PRE_REGISTERED                         = 'event.preregistered';
    const EVENT_LOCALE_CHANGED                         = 'event.locale_changed';
    const EVENT_KEY_DATES_UPDATED                      = 'event.key_dates.updated';
    const USER_REGISTRATION                            = 'user.registration'; // First step completed
    const USER_REGISTERED                              = 'user.registered';
    const USER_RESET_PASSWORD_CONFIRMED                = 'user.reset_password.confirm';
    const ORDER_CONFIRMED                              = 'order.confirm';
    const ORDER_UPDATED                                = 'order.updated';
    const ADMIN_ACCOUNT_ACTIVATED                      = 'admin.account_activated';
    const ADMIN_PASSWORD_RESET                         = 'admin.password_reset';
    const USER_ACCOUNT_ACTIVATED                       = 'user.account_activated';
    const USER_PASSWORD_RESET                          = 'user.password_reset';
    const USER_PROFILE_COMPLETED                       = 'user.profile_completed';
    const REGISTRATION_STEP                            = 'user.registration.step';
    const MUST_SELECT_PACKAGE                          = 'package.must_select_package';
    const REGISTRATION_TEMPLATE_UPDATED                = 'registration_template.updated';
    const SHEET_TEMPLATE_UPDATED                       = 'sheet_template.updated';
    const PACKAGE_STEP_DONE                            = 'package.step.done';
    const TRANSACTION_CREATED                          = 'transaction.created';
    const TRANSACTION_UPDATED                          = 'transaction.updated';
    const TRANSACTION_REMOVED                          = 'transaction.removed';
    const TRANSACTION_CONFIRMED                        = 'transaction.confirm';
    const PARTICIPANT_IMPORTED                         = 'participant.imported';
    const PARTICIPANT_ADDED                            = 'participant.added';
    const PARTICIPANT_REMOVED                          = 'participant.removed';
    const HAPPENING_PARTICIPATED                       = 'happening.participated';
    const HAPPENING_PARTICIPATE                        = 'happening.participate';
    const HAPPENING_UN_PARTICIPATE                     = 'happening.un_participate';
    const HAPPENING_TYPES_UPDATED                      = 'happening.types.updated';
    const UNAVAILABILITY_ADDED                         = 'unavailability.added';
    const UNAVAILABILITY_REMOVED                       = 'unavailability.removed';
    const REQUEST_PARTICIPATE                          = 'request.participate';
    const REQUEST_UN_PARTICIPATE                       = 'request.un_participate';
    const MASS_ASSIGNMENT_UPDATED                      = 'mass.assignment.updated';
    const USER_AGENDA_CONFIRMED                        = 'user.agenda.confirmed';
    const USER_EVENT_TOKEN_AGENDA_CONFIRMATION_CREATED = 'user_event_token.agenda_confirmation.created';
    const USER_AGENDA_CONFIRMATION_STATUS_UPDATED      = 'user_event_token.agenda_confirmation.updated';
    const MASS_UNAVAILABILITY_DISPATCHED               = 'mass.unavailability.dispatched';
    const SLOT_GENERATED                               = 'slot.generated';
    const SLOT_TOGGLE_LOCKED                           = 'slot.toggle.locked';
    const SLOT_DELETED                                 = 'slot.deleted';
    const USER_PHONE_VALIDATED                         = 'user.phone_validated';
    const USER_AVAILABILITY_CONFIRMED                  = 'user.availability.confirmed';
    const TIP_ASSIGNED                                 = 'tip.assigned';
    const TIP_REMOVED_FROM_EVENT                       = 'tip.removed_from_event';
    const TIP_EVENT_CREATED                            = 'tip.event.created';
    const TIP_EVENT_UPDATED                            = 'tip.event.updated';
}
