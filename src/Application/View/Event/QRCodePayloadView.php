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

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var null|string */
    public $sheetTitle;

    public function __construct(string $payload, string $firstName, string $lastName, ?string $sheetTitle)
    {
        $this->payload = $payload;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->sheetTitle = $sheetTitle;
    }
}
