<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Token;

class UserEventTokenType
{
    const AGENDA_CONFIRMED = 'agenda_confirmed';

    /**
     * @return array of UserEventToken type
     */
    public static function getUserEventTokenType()
    {
        return [
            self::AGENDA_CONFIRMED,
        ];
    }
}
