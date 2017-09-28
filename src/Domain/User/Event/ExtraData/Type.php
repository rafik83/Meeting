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
    const LENI_FINGERPRINT = 'leni_fingerprint';

    /**
     * DateTime of the last notification sent to user
     */
    const MEETING_REQUEST_DATE_LAST_NOTIFICATION_REMINDER = 'meeting_request_date_last_notification_reminder';
}
