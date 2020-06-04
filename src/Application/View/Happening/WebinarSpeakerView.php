<?php

namespace Proximum\Vimeet\Application\View\Happening;

class WebinarSpeakerView
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
    public $organization;

    public function __construct(
        int $userId,
        ?string $firstName,
        ?string $lastName,
        ?string $position,
        ?string $organization
    ) {
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->position = $position;
        $this->organization = $organization;
    }
}
