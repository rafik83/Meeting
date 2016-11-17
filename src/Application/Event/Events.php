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
    const SHEET_VALIDATED                      = 'sheet.validated';
    const SHEET_ACCEPTED                       = 'sheet.accepted';
    const SHEET_INVITATION_CLOSE_TO_EXPIRATION = 'sheet.invitation.close_to_expiration';
    const SHEET_ADD_PARTICIPANT_CONFIRMATION   = 'sheet.participant.add.confirmation';
    const SHEET_CHANGED_TYPE                   = 'sheet.changed_type';
    const SHEET_VALIDATION_DRAFT               = 'sheet.validation.draft';
    const SHEET_VALIDATION_PENDING             = 'sheet.validation.pending';
    const SHEET_VALIDATION_VALIDATE            = 'sheet.validation.validate';
    const USER_MAIL_CHANGED                    = 'change_mail';
    const MEETING_PARTICIPANT_ADDED            = 'meeting.participant.added';
    const MEETING_PARTICIPANT_REMOVED          = 'meeting.participant.removed';
    const REQUEST_SENT                         = 'meeting_request.sent';
    const REQUEST_REFUSED                      = 'meeting_request.refused';
    const REQUEST_CANCELED                     = 'meeting_request.canceled';
    const REQUEST_ACCEPTED                     = 'meeting_request.accepted';
    const MEETING_CANCELED                     = 'meeting.canceled';
    const REQUEST_PARTICIPANT_ADDED            = 'meeting_request.participant.added';
    const REQUEST_PARTICIPANT_REMOVED          = 'meeting_request.participant.removed';
    const REQUEST_UPDATE_MESSAGE               = 'meeting_request.update.message';
    const MEETING_UPDATE_MESSAGE               = 'meeting.update.message';
    const USER_REGISTERED                      = 'user.registered';
    const EVENT_PRE_REGISTERED                 = 'event.preregistered';
    const EVENT_LOCALE_CHANGED                 = 'event.locale_changed';
    const USER_RESET_PASSWORD_CONFIRMED        = 'user.reset_password.confirm';
    const ORDER_CONFIRMED                      = 'order.confirm';
    const ADMIN_ACCOUNT_ACTIVATED              = 'admin.account_activated';
    const ADMIN_PASSWORD_RESET                 = 'admin.password_reset';
    const USER_ACCOUNT_ACTIVATED               = 'user.account_activated';
    const USER_PASSWORD_RESET                  = 'user.password_reset';
    const USER_PROFILE_COMPLETED               = 'user.profile_completed';
    const TRANSACTION_CONFIRMED                = 'transaction.confirm';
    const SHEET_ENABLE_DISABLE                 = 'sheet.enable_disable';
    const SHEET_CATALOG                        = 'sheet.catalog';
    const REGISTRATION_STEP                    = 'user.registration.step';
    const SHEET_UPDATED                        = 'sheet.updated';
    const MUST_SELECT_PACKAGE                  = 'package.must_select_package';
    const REGISTRATION_TEMPLATE_UPDATED        = 'registration_template.updated';
    const PACKAGE_STEP_DONE                    = 'package.step.done';
    const SHEET_COMPLETENESS                   = 'sheet.completeness';
    const TRANSACTION_CREATED                  = 'transaction.created';
    const TRANSACTION_UPDATED                  = 'transaction.updated';
    const TRANSACTION_REMOVED                  = 'transaction.removed';
}
