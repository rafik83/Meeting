<?php

namespace Proximum\Vimeet\Application\Event\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class ParticipantRemovedEvent extends Event
{
    /** @var Sheet */
    public $sheet;

    /** @var User[] */
    public $usersRemovedFromSheet;

    /**
     * @param Sheet  $sheet
     * @param User[] $usersRemovedFromSheet
     */
    public function __construct(Sheet $sheet, array $usersRemovedFromSheet = [])
    {
        $this->sheet = $sheet;
        $this->usersRemovedFromSheet = $usersRemovedFromSheet;
    }
}
