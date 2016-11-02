<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToUnRefuseMeetingRequestException;
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
     * @var RequestPermissionManager
     */
    private $permissionManager;

    /**
     * @param Meeting\RequestRepositoryInterface $meetingRequestRepository
     * @param RequestPermissionManager           $permissionManager
     * @param \DateTimeInterface                 $dateTime
     */
    public function __construct(
        Meeting\RequestRepositoryInterface $meetingRequestRepository,
        RequestPermissionManager $permissionManager,
        \DateTimeInterface $dateTime
    ) {
        $this->meetingRequestRepository = $meetingRequestRepository;
        $this->permissionManager        = $permissionManager;
        $this->dateTime                 = $dateTime;
    }

    /**
     * @param UnRefuseMeetingRequest $unRefuseMeetingRequest
     *
     * @throws IsNotAllowedToUnRefuseMeetingRequestException
     */
    public function handle(UnRefuseMeetingRequest $unRefuseMeetingRequest)
    {
        if (!$this->permissionManager->isAllowedToUnRefuse(
            $unRefuseMeetingRequest->editor,
            $unRefuseMeetingRequest->meetingRequest,
            $unRefuseMeetingRequest->sheet
        )) {
            throw new IsNotAllowedToUnRefuseMeetingRequestException();
        }
        $unRefuseMeetingRequest->meetingRequest->unRefuse($this->dateTime);
        $this->meetingRequestRepository->set($unRefuseMeetingRequest->meetingRequest);
    }
}
