<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Messaging\SMS\SMS;

interface SMSSenderInterface
{
    /**
     * @param SMS $sms
     */
    public function send(SMS $sms);
}
