<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData;

use Proximum\Vimeet\Domain\Model\Event;

class SendingRequestData
{
    /** @var Event */
    public $event;

    /** @var array */
    public $data;

    public function __construct(Event $event, array $data)
    {
        $this->event = $event;
        $this->data = $data;
    }
}
