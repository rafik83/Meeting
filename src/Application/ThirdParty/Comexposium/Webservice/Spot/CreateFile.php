<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot;

use Proximum\Vimeet\Domain\Model\Event;

class CreateFile
{
    /** @var Event */
    public $event;

    /** @var string */
    public $content;

    public function __construct(Event $event, string $content)
    {
        $this->event = $event;
        $this->content = $content;
    }
}
