<?php

namespace Proximum\Vimeet\Domain\Token;

class UserEventTokenType
{
    const AGENDA_CONFIRMATION = 'agenda_confirmation';
    const EBADGE_CONFIRMATION = 'download_edbade';

    /**
     * @return array of UserEventToken type
     */
    public static function getUserEventTokenType()
    {
        return [
            self::AGENDA_CONFIRMATION,
        ];
    }
}
