<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event\ExtraParameter;

/**
 *
 * All the types used for the $type of Event\ExtraParameter
 *
 * @see Proximum\Vimeet\Domain\Model\Event\ExtraParameter
 */
class Type
{
    const TYPE_LENI_USER = 'leni_user';
    const TYPE_LENI_EVENT = 'leni_event';

    const TYPES = [
        self::TYPE_LENI_USER,
        self::TYPE_LENI_EVENT,
    ];
}
