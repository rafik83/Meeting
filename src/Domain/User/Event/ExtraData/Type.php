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
}
