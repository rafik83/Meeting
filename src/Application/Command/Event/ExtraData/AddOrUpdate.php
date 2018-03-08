<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\ExtraData;

use Proximum\Vimeet\Domain\Model\Event;

class AddOrUpdate
{
    /** @var Event */
    public $event;

    /** @var string */
    public $name;

    /** @var null|string */
    public $value;

    public function __construct(Event $event, string $name, ?string $value)
    {
        $this->event = $event;
        $this->name = $name;
        $this->value = $value;
    }
}
