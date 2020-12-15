<?php

namespace Proximum\Vimeet\Application\View\Happening;

class WebinarParticipantView
{
    /** @var int */
    public $userId;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string */
    public $position;

    /** @var string|null */
    public $sheetTitle;

    public function __construct(
        int $userId,
        ?string $firstName,
        ?string $lastName,
        ?string $position,
        ?string $sheetTitle
    ) {
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->position = $position;
        $this->sheetTitle = $sheetTitle;
    }
}
