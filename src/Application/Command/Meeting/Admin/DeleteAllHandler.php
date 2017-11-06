<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Exception\Meeting\NotAllowedToDeleteAllMeetingsException;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class DeleteAllHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var MeetingPublishedAccessChecker
     */
    private $meetingPublishedAccessChecker;

    /**
     * @var JobQueueInterface
     */
    private $jobQueue;

    /**
     * @param MeetingRepositoryInterface    $meetingRepository
     * @param MeetingPublishedAccessChecker $meetingPublishedAccessChecker
     * @param JobQueueInterface             $jobQueue
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker,
        JobQueueInterface $jobQueue
    ) {
        $this->meetingRepository             = $meetingRepository;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
        $this->jobQueue                      = $jobQueue;
    }

    /**
     * @param DeleteAll $deleteAll
     *
     * @throws NotAllowedToDeleteAllMeetingsException
     */
    public function handle(DeleteAll $deleteAll)
    {
        if ($this->meetingPublishedAccessChecker->allowedToAccess($deleteAll->event)) {
            throw new NotAllowedToDeleteAllMeetingsException('The meetings are published, you can not delete them');
        }

        $this->meetingRepository->deleteAll($deleteAll->event);

        $this->jobQueue->indexInCatalogSheetsByEvent($deleteAll->event);
        $this->jobQueue->aggregatePhoneValidationStatus($deleteAll->event);
    }
}
