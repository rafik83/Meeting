<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Badge;

class UserBadgeByEventView
{
    /** @var null|string */
    public $sheetTitle;

    /** @var null|string */
    public $firstName;

    /** @var null|string */
    public $lastName;

    /** @var null|string */
    public $position;

    /** @var null|string */
    public $participationType;

    /** @var null|string */
    public $qrCodeIdentifier;

    /** @var null|string */
    public $qrCodeImage;

    public function __construct(
        ?string $sheetTitle,
        ?string $firstName,
        ?string $lastName,
        ?string $position,
        ?string $participationType,
        ?string $qrCodeIdentifier,
        ?string $qrCodeImage
    ) {
        $this->sheetTitle = $sheetTitle;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->position = $position;
        $this->participationType = $participationType;
        $this->qrCodeIdentifier = $qrCodeIdentifier;
        $this->qrCodeImage = $qrCodeImage;
    }
}
