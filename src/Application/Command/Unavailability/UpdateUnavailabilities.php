<?php

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class UpdateUnavailabilities implements Command
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var string */
    public $locale;

    /** @var Participant */
    public $participant;

    /** @var array */
    public $payload;

    public function __construct(
        Event $event,
        Sheet $sheet,
        User $user,
        string $locale,
        Participant $participant,
        array $payload
    ) {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->user = $user;
        $this->locale = $locale;
        $this->participant = $participant;
        $this->payload = $payload;
    }
}
