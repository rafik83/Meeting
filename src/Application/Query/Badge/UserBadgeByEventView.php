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
    public $participationTypeOrCategoryLabel;

    /** @var null|string */
    public $qrCodeIdentifier;

    /** @var null|string */
    public $qrCodeImage;

    /** @var null|string */
    public $header;

    /** @var string */
    public $footerTextColor;

    /** @var string */
    public $footerColor;

    /** @var null|string */
    public $country;

    public function __construct(
        ?string $sheetTitle,
        ?string $firstName,
        ?string $lastName,
        ?string $position,
        ?string $participationTypeOrCategoryLabel,
        ?string $qrCodeIdentifier,
        ?string $qrCodeImage,
        ?string $header,
        string $footerTextColor,
        string $footerColor,
        ?string $country
    ) {
        $this->sheetTitle = $sheetTitle;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->position = $position;
        $this->participationTypeOrCategoryLabel = $participationTypeOrCategoryLabel;
        $this->qrCodeIdentifier = $qrCodeIdentifier;
        $this->qrCodeImage = $qrCodeImage;
        $this->header = $header;
        $this->footerTextColor = $footerTextColor;
        $this->footerColor = $footerColor;
        $this->country = $country;
    }
}
