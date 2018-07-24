<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Event;

class QRCodePayloadView
{
    /** @var string */
    public $payload;

    public function __construct(string $payload)
    {
        $this->payload = $payload;
    }
}
