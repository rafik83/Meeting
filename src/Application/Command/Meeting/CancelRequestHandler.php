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
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToCancelMeetingRequestException;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class CancelRequestHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var RequestPermissionManager
     */
    private $permissionManager;

    /**
     * CancelRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param RequestPermissionManager   $permissionManager
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        RequestPermissionManager $permissionManager
    ) {
        $this->requestRepository = $requestRepository;
        $this->permissionManager = $permissionManager;
    }

    /**
     * @param CancelRequest $cancelRequest
     *
     * @throws IsNotAllowedToCancelMeetingRequestException
     */
    public function handle(CancelRequest $cancelRequest)
    {
        if (!$this->permissionManager->isAllowedToCancel(
            $cancelRequest->emitter,
            $cancelRequest->request,
            $cancelRequest->sheet
        )) {
            throw new IsNotAllowedToCancelMeetingRequestException();
        }

        $this->requestRepository->remove($cancelRequest->request);
    }
}
