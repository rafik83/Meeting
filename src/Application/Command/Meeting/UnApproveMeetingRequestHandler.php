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
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToUnApproveMeetingRequestException;
use Proximum\Vimeet\Domain\Repository\Meeting;

class UnApproveMeetingRequestHandler
{
    /**
     * @var Meeting\RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var RequestPermissionManager
     */
    private $permissionManager;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @param Meeting\RequestRepositoryInterface $requestRepository
     * @param RequestPermissionManager           $permissionManager
     * @param \DateTimeInterface                 $dateTime
     */
    public function __construct(
        Meeting\RequestRepositoryInterface $requestRepository,
        RequestPermissionManager $permissionManager,
        \DateTimeInterface $dateTime
    ) {
        $this->requestRepository = $requestRepository;
        $this->permissionManager = $permissionManager;
        $this->dateTime          = $dateTime;
    }

    /**
     * @param UnApproveMeetingRequest $unApproveMeetingRequest
     *
     * @throws IsNotAllowedToUnApproveMeetingRequestException
     */
    public function handle(UnApproveMeetingRequest $unApproveMeetingRequest)
    {
        if (!$this->permissionManager->isAllowedToUnApprove(
            $unApproveMeetingRequest->user,
            $unApproveMeetingRequest->meetingRequest,
            $unApproveMeetingRequest->sheet
        )) {
            throw new IsNotAllowedToUnApproveMeetingRequestException();
        }

        $unApproveMeetingRequest->meetingRequest->unApprove($this->dateTime);
        $this->requestRepository->set($unApproveMeetingRequest->meetingRequest);
    }
}
