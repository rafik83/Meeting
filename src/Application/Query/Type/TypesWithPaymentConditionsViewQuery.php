<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Type;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class TypesWithPaymentConditionsViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /**
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(Event $event, string $locale)
    {
        $this->event = $event;
        $this->locale = $locale;
    }
}
