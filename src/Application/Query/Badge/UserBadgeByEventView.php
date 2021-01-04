<?php

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

    /** @var bool */
    public $isMirrored;

    /** @var string|null */
    public $leftImage;

    /** @var string|null */
    public $rightImage;

    /** @var bool */
    public $isRightImageFullHeight;

    /** @var string|null */
    public $headerLeftColor;

    /** @var string|null */
    public $headerRightColor;

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
        ?string $country,
        bool $isMirrored,
        ?string $leftImage,
        ?string $rightImage,
        bool $isRightImageFullHeight,
        ?string $headerLeftColor,
        ?string $headerRightColor
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
        $this->isMirrored = $isMirrored;
        $this->leftImage = $leftImage;
        $this->rightImage = $rightImage;
        $this->isRightImageFullHeight = $isRightImageFullHeight;
        $this->headerLeftColor = $headerLeftColor;
        $this->headerRightColor = $headerRightColor;
    }
}
