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
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CancelHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * CancelHandler constructor.
     *
     * @param MeetingRepositoryInterface      $meetingRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(MeetingRepositoryInterface $meetingRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->meetingRepository = $meetingRepository;
        $this->eventDispatcher   = $eventDispatcher;
    }

    /**
     * @param Cancel $cancel
     */
    public function handle(Cancel $cancel)
    {
        $this->meetingRepository->set($cancel->meeting->cancel());
        $this->eventDispatcher->dispatch('meeting.canceled', new CanceledEvent($cancel->user, $cancel->meeting, $cancel->date, $cancel->message));
    }
}
