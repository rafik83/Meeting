<?php

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Application\Exception\Meeting\MoveMeetingSlotException;
use Proximum\Vimeet\Domain\Meeting\CanMoveMeeting;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MoveHandler
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var CanMoveMeeting */
    private $canMoveMeeting;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        CanMoveMeeting $canMoveMeeting,
        CommandBusInterface $commandBus,
        TranslatorInterface $translator
    ) {
        $this->canMoveMeeting = $canMoveMeeting;
        $this->commandBus = $commandBus;
        $this->translator = $translator;
    }

    public function handle(Move $move): void
    {
        if (false === $this->canMoveMeeting->isSatisfiedBy($move->sheet)) {
            throw new AccessDeniedException();
        }

        try {
            $this->commandBus->handle(new UpdateSlot($move->meeting, $move->meetingSlot));

            if ($move->content) {
                //$this->commandBus->handle(new UpdateSlot($move->meeting, $move->meetingSlot));
            }
        } catch (\Exception $exception) {
            throw new MoveMeetingSlotException(
                $this->translator->trans('agenda.meeting.updateSlot.error')
            );
        }
    }
}
