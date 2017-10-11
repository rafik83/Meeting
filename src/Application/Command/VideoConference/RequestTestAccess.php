<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\VideoConference;

class RequestTestAccess
{
    /** @var string */
    public $sessionId;

    public function __construct(string $sessionId)
    {
        $this->sessionId = $sessionId;
    }
}
