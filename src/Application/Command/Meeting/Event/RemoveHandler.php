<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Event;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingRemovedEvent;
use Proximum\Vimeet\Application\Exception\Meeting\RemoveMeetingException;
use Proximum\Vimeet\Domain\Meeting\CanRemoveMeeting;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoveHandler
{
    /**@var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    /** @var \DateTimeInterface */
    private $datetime;

    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var CanRemoveMeeting */
    private $canRemoveMeeting;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        MeetingRepositoryInterface $meetingRepository,
        DelayedEventDispatcherInterface $eventDispatcher,
        CanRemoveMeeting $canRemoveMeeting,
        \DateTimeInterface $datetime
    ) {
        $this->meetingRepository  = $meetingRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->datetime = $datetime;
        $this->messageRepository = $messageRepository;
        $this->canRemoveMeeting = $canRemoveMeeting;
    }

    /**
     * @param Remove $command
     *
     * @throws RemoveMeetingException
     */
    public function handle(Remove $command)
    {
        if (false === $this->canRemoveMeeting->isSatisfiedBy($command->sheet)
            || !$command->meeting->hasSheet($command->sheet)
        ) {
            throw new AccessDeniedException();
        }

        try {
            if ($command->content) {
                $message = new Message(
                    $command->meeting->getRequest(),
                    $command->sheet,
                    $command->content,
                    $this->datetime
                );
                $this->messageRepository->add($message);
            }

            $this->meetingRepository->remove($command->meeting);
        } catch (\Exception $exception){
            throw new RemoveMeetingException(
                'Can not remove meeting'
            );
        }

        $this->eventDispatcher->dispatch(
            Events::MEETING_REMOVED,
            new MeetingRemovedEvent(
                [
                    $command->meeting->getFromSheet(),
                    $command->meeting->getToSheet(),
                ],
                $command->meeting->getAllParticipants()
            )
        );
    }
}
