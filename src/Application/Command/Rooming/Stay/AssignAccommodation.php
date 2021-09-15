<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Stay;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class AssignAccommodation implements Command
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var \DateTimeInterface */
    public $arrival;

    /** @var \DateTimeInterface */
    public $departure;

    /** @var null|Accommodation */
    public $accommodation;

    /** @var string */
    public $roomType;

    /** @var string */
    public $roomNumber = '';

    /** @var null|User */
    public $roommate;

    /** @var null|Sheet */
    public $otherSheet;

    public function __construct(
        Event $event,
        User $user,
        \DateTimeInterface $arrival,
        \DateTimeInterface $departure,
        ?Sheet $otherSheet
    ) {
        $this->event = $event;
        $this->user = $user;
        $this->arrival = $arrival;
        $this->departure = $departure;
        $this->roomType = Stay::ROOM_TYPE_SINGLE;
        $this->otherSheet = $otherSheet;
    }
}
