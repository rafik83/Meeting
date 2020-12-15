<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\User\Event\ExtraData;

final class Type
{
    // Fingerprint ot the payload sent to LENI
    const LENI_FINGERPRINT = 'leni_fingerprint';

    // Fingerprint of the payload pending to be sent to LENI
    const LENI_FINGERPRINT_PENDING = 'leni_fingerprint_pending';

    // Id of the user retrieved from the api
    const LENI_USER_ID = 'leni_user_id';

    /**
     * DateTime of the last notification sent to user
     */
    const MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER = 'meeting_request_date_last_notification_reminder';

    /**
     * State to confirm the availability of a user
     */
    const AVAILABILITY_CONFIRMATION = 'availability_confirmed';

    /**
     * State to ignore the display of the phone confirmation
     */
    const PHONE_CONFIRMATION_IGNORED = 'phone_confirmation_ignored';

    // User imported from Comexposium. Need to use the Comexposium SSO to login
    const IMPORTED_FROM_COMEXPOSIUM = 'imported_from_comexposium';

    /**
     * User imported from the Tech event Webservice, value of the IDCONTACT
     */
    public const IMPORTED_FROM_TECH_EVENT = 'imported_from_tech_event';
    public const TECH_EVENT_IDENTIFIER_MD5 = 'tech_event_identifier_md5';

    /**
     * Data to use to log the user from Tech Event.
     */
    public const TECH_EVENT_LOGIN_DATA = 'tech_event_login_data';

    /**
     * Protected key to encrypt / decrypt files for this user in the event
     */
    public const PROTECTED_KEY = 'protected_key';

    public const VISIO_TESTED = 'visio_tested';

    public const IS_PARTICIPANT_VISIO = 'is_participant_visio';

    public const PLANNING_MODIFIED = 'has_planning_modified';

    /**
     * Rooming
     */
    public const ROOMING_COMMENT = 'rooming_comment';

    public const ROOMING_TASTING = 'rooming_tasting';
}
