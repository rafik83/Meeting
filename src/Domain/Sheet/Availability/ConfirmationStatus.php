<?php

namespace Proximum\Vimeet\Domain\Sheet\Availability;

final class ConfirmationStatus
{
    const NONE_CONFIRMED = 'none_confirmed';
    const AT_LEAST_ONE_CONFIRMED = 'at_least_one_confirmed';
    const ALL_CONFIRMED = 'all_confirmed';

    const ALL_STATUS = [
        self::ALL_CONFIRMED,
        self::AT_LEAST_ONE_CONFIRMED,
        self::NONE_CONFIRMED,
    ];
}
