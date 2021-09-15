<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Participant;

use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantView
{
    /** @var Participant */
    public $participant;

    /** @var string|null */
    public $firstName;

    /** @var string|null */
    public $lastName;

    /** @var string|null */
    public $position;

    /** @var string|null */
    public $avatar;

    public function __construct(Participant $participant, ?string $firstName, ?string $lastName, ?string $position, ?string $avatar)
    {
        $this->participant = $participant;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->position = $position;
        $this->avatar = $avatar;
    }

    public function getSheetTitle(): ?string
    {
        return $this->participant->getSheet()->getTitle();
    }
}
