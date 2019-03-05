<?php

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Domain\Meeting\CanMoveMeeting;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MoveHandler
{
    /** @var CommandBusInterface */
    private $commandBus;
    /** @var CanMoveMeeting */
    private $canMoveMeeting;

    public function __construct(
        CanMoveMeeting $canMoveMeeting,
        CommandBusInterface $commandBus
    ) {
        $this->canMoveMeeting = $canMoveMeeting;
        $this->commandBus = $commandBus;
    }

    public function handle(Move $move)
    {
        if (false === $this->canMoveMeeting->isSatisfiedBy($move->sheet)) {
            throw new AccessDeniedException();
        }

        $this->commandBus->handle(new UpdateSlot($move->meeting, $move->meetingSlot));
    }
}
