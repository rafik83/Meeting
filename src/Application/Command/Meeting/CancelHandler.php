<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Event\Meeting\CanceledEvent;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CancelHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * CancelHandler constructor.
     *
     * @param MeetingRepositoryInterface $meetingRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param EventDispatcherInterface   $eventDispatcher
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        MessageRepositoryInterface $messageRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->eventDispatcher   = $eventDispatcher;
    }

    /**
     * @param Cancel $cancel
     */
    public function handle(Cancel $cancel)
    {
        // Cancel meeting
        $this->meetingRepository->set($cancel->meeting->cancel());

        // Add message
        $this->messageRepository->add(new Message(
            $cancel->meeting,
            $cancel->sheet,
            $cancel->message,
            $cancel->date
        ));

        // Disptach event
        $this->eventDispatcher->dispatch(
            'meeting.canceled',
            new CanceledEvent(
                $cancel->user,
                $cancel->meeting,
                $cancel->date,
                $cancel->message
            )
        );
    }
}
