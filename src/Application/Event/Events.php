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
    const SHEET_VALIDATED             = 'sheet.validated';
    const SHEET_ACCEPTED              = 'sheet.accepted';
    const USER_MAIL_CHANGED           = 'change_mail';
    const MEETING_PARTICIPANT_ADDED   = 'meeting.participant.added';
    const MEETING_PARTICIPANT_REMOVED = 'meeting.participant.removed';
    const REQUEST_SENT                = 'meeting_request.sent';
    const REQUEST_REFUSED             = 'meeting_request.refused';
    const REQUEST_CANCELED            = 'meeting_request.canceled';
    const REQUEST_ACCEPTED            = 'meeting_request.accepted';
    const MEETING_CANCELED            = 'meeting.canceled';
    const REQUEST_PARTICIPANT_ADDED   = 'meeting_request.participant.added';
    const REQUEST_PARTICIPANT_REMOVED = 'meeting_request.participant.removed';
    const REQUEST_UPDATE_MESSAGE      = 'meeting_request.update.message';
    const MEETING_UPDATE_MESSAGE      = 'meeting.update.message';
    const USER_REGISTERED             = 'user.registered';
    const EVENT_PRE_REGISTERED        = 'event.preregistered';
}
