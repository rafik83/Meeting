<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;

class MonologSMSSenderAdapter implements SMSSenderInterface
{
    public function send(SMS $sms)
    {

    }
}
