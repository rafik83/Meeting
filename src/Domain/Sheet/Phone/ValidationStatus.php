<?php

namespace Proximum\Vimeet\Domain\Sheet\Phone;

/**
 * This class is used to determine the possible status of the phone validation of a sheet
 */
final class ValidationStatus
{
    const NOT_CONCERNED    = 'phone_not_concerned';
    const ALL_CONFIRMED    = 'phone_all_confirmed';
    const PARTLY_CONFIRMED = 'phone_partly_confirmed';
    const NONE_CONFIRMED   = 'phone_none_confirmed';

    const ALL_CONCERNED_STATUS = [
        self::ALL_CONFIRMED,
        self::PARTLY_CONFIRMED,
        self::NONE_CONFIRMED,
    ];
}
