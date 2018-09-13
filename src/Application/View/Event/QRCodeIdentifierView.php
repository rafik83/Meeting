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

    /** @var null|string */
    public $participationType;

    /** @var null|\DateTime */
    public $checkin;

    public function __construct(
        string $identifier,
        string $firstName,
        string $lastName,
        ?string $sheetTitle,
        ?string $participationType,
        ?\DateTime $checkin = null
    ) {
        $this->identifier = $identifier;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->sheetTitle = $sheetTitle;
        $this->participationType = $participationType;
        $this->checkin = $checkin;
    }

    public function setSheetTitle(?string $sheetTitle): void
    {
        $this->sheetTitle = $sheetTitle;
    }

    public function setParticipationType(?string $participationType): void
    {
        $this->participationType = $participationType;
    }
}
