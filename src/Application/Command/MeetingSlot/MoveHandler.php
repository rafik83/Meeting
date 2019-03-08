<?php

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Application\Exception\Meeting\MoveMeetingSlotException;
use Proximum\Vimeet\Domain\Meeting\CanMoveMeeting;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MoveHandler
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var CanMoveMeeting */
    private $canMoveMeeting;

    /** @var TranslatorInterface */
    private $translator;

    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var \DateTimeInterface */
    private $datetime;

    public function __construct(
        CanMoveMeeting $canMoveMeeting,
        CommandBusInterface $commandBus,
        TranslatorInterface $translator,
        MessageRepositoryInterface $messageRepository,
        MeetingRepositoryInterface $meetingRepository,
        \DateTimeInterface $datetime
    ) {
        $this->canMoveMeeting = $canMoveMeeting;
        $this->commandBus = $commandBus;
        $this->translator = $translator;
        $this->messageRepository = $messageRepository;
        $this->meetingRepository = $meetingRepository;
        $this->datetime = $datetime;
    }

    public function handle(Move $move): void
    {
        if (false === $this->canMoveMeeting->isSatisfiedBy($move->sheet)) {
            throw new AccessDeniedException();
        }

        try {
            $this->commandBus->handle(
                new UpdateSlot(
                    $move->meeting,
                    $move->meetingSlot,
                    $move->meeting->getSpot()->isVisio(),
                    true
                )
            );

            $move->meeting->blockSlot();
            $this->meetingRepository->set($move->meeting);

            if ($move->content) {
                $message = new Message(
                    $move->meeting->getRequest(),
                    $move->sheet,
                    $move->content,
                    $this->datetime
                );

                $this->messageRepository->add($message);
            }
        } catch (\Exception $exception) {
            throw new MoveMeetingSlotException(
                $this->translator->trans('agenda.meeting.updateSlot.error')
            );
        }
    }
}
