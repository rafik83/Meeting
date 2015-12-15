<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class CancelHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * CancelHandler constructor.
     *
     * @param MeetingRepositoryInterface $meetingRepository
     */
    public function __construct(MeetingRepositoryInterface $meetingRepository)
    {
        $this->meetingRepository = $meetingRepository;
    }

    /**
     * @param Cancel $cancel
     */
    public function handle(Cancel $cancel)
    {
        $cancel->meeting->cancel();

        $this->meetingRepository->set($cancel->meeting);
    }
}
