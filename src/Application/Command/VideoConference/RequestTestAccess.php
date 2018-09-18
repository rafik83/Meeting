<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\VideoConference;

use Proximum\Vimeet\Application\Command\Command;

class RequestTestAccess implements Command
{
    /** @var string */
    public $sessionId;

    public function __construct(string $sessionId)
    {
        $this->sessionId = $sessionId;
    }
}
