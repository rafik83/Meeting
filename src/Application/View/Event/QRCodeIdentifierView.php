<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Event;

class QRCodeIdentifierView
{
    /** @var string */
    public $identifier;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var null|string */
    public $sheetTitle;

    /** @var string */
    public $badgeUrl;

    public function __construct(
        string $identifier,
        string $firstName,
        string $lastName,
        ?string $sheetTitle,
        string $badgeUrl
    ) {
        $this->identifier = $identifier;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->sheetTitle = $sheetTitle;
        $this->badgeUrl = $badgeUrl;
    }

    public function setSheetTitle(?string $sheetTitle): void
    {
        $this->sheetTitle = $sheetTitle;
    }
}
