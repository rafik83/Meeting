<?php

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

    /** @var string */
    public $badgeUrl;

    /** @var string */
    public $planningUrl;

    public function __construct(
        string $identifier,
        string $firstName,
        string $lastName,
        ?string $sheetTitle,
        ?string $participationType,
        ?\DateTime $checkin = null,
        string $badgeUrl,
        string $planningUrl
    ) {
        $this->identifier = $identifier;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->sheetTitle = $sheetTitle;
        $this->participationType = $participationType;
        $this->checkin = $checkin;
        $this->badgeUrl = $badgeUrl;
        $this->planningUrl = $planningUrl;
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
