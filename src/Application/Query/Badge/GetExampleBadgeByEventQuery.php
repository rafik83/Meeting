<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Badge;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class GetExampleBadgeByEventQuery extends AbstractGetBadgeByEventQuery
{
    /** @var Type */
    public $type;

    public function __construct(Event $event, Type $type)
    {
        parent::__construct($event);
        $this->type = $type;
    }
}
