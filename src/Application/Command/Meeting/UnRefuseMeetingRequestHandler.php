<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Repository\Meeting;

class UnRefuseMeetingRequestHandler
{
    /**
     * @var Meeting\RequestRepositoryInterface
     */
    private $meetingRequestRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @param Meeting\RequestRepositoryInterface $meetingRequestRepository
     * @param \DateTimeInterface                 $dateTime
     */
    public function __construct(
        Meeting\RequestRepositoryInterface $meetingRequestRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->meetingRequestRepository = $meetingRequestRepository;
        $this->dateTime                 = $dateTime;

    }

    /**
     * @param UnRefuseMeetingRequest $unRefuseMeetingRequest
     */
    public function handle(UnRefuseMeetingRequest $unRefuseMeetingRequest)
    {
        $this->meetingRequestRepository->set($unRefuseMeetingRequest->meetingRequest->unRefuse($this->dateTime));
    }
}
