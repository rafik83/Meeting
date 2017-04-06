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
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\MeetingRequest\CancelRequestEvent;
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToCancelMeetingRequestException;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

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
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * CancelRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param RequestPermissionManager   $permissionManager
     * @param DelayedEventDispatcher     $eventDispatcher
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        RequestPermissionManager $permissionManager,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->requestRepository = $requestRepository;
        $this->permissionManager = $permissionManager;
        $this->eventDispatcher = $eventDispatcher;
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

        $request = clone $cancelRequest->request;

        $this->requestRepository->remove($cancelRequest->request);

        $this->eventDispatcher->dispatch(
            Events::MEETING_REQUEST_CANCELED,
            new CancelRequestEvent($request)
        );
    }
}
