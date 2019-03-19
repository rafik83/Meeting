<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Slot\DeletedEvent;
use Proximum\Vimeet\Application\Exception\Slot\IsNotAllowedToRemoveSlotException;
use Proximum\Vimeet\Domain\Meeting\CanRemoveMeeting;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoveHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    public $meetingSlotRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var CanRemoveMeeting
     */
    private $canRemoveMeeting;

    /**
     * RemoveHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface  $meetingSlotRepository
     * @param MessageRepositoryInterface      $messageRepository
     * @param MeetingRepositoryInterface      $meetingRepository
     * @param DelayedEventDispatcherInterface $delayedEventDispatcher
     * @param CanRemoveMeeting                $canRemoveMeeting
     * @param \DateTimeInterface              $datetime
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        MessageRepositoryInterface $messageRepository,
        MeetingRepositoryInterface $meetingRepository,
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        CanRemoveMeeting $canRemoveMeeting,
        \DateTimeInterface $datetime
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->meetingRepository  = $meetingRepository;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->datetime = $datetime;
        $this->messageRepository = $messageRepository;
        $this->canRemoveMeeting = $canRemoveMeeting;
    }

    /**
     * @param Remove $command
     *
     * @throws IsNotAllowedToRemoveSlotException
     */
    public function handle(Remove $command)
    {

        if (false === $this->canRemoveMeeting->isSatisfiedBy($command->sheet)) {
            throw new AccessDeniedException();
        }

        if(true === $this->canRemoveMeeting->isSatisfiedBy($command->sheet)) {
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
        }

        $this->meetingSlotRepository->remove($command->meetingSlot);

        $this->delayedEventDispatcher->dispatch(
            Events::SLOT_DELETED,
            new DeletedEvent($command->meetingSlot->getEvent())
        );
    }
}
