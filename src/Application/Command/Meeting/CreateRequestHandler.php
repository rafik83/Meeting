<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\MeetingRequest\CreateRequestEvent;
use Proximum\Vimeet\Application\View\Meeting\ApproveRequestResult;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class CreateRequestHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var ApproveRequestHandler
     */
    private $approveRequestHandler;

    /**
     * @var RequestPermissionManager
     */
    private $requestPermissionManager;

    public function __construct(
        ApproveRequestHandler $approveRequestHandler,
        RequestPermissionManager $requestPermissionManager,
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->requestRepository = $requestRepository;
        $this->messageRepository = $messageRepository;
        $this->eventDispatcher   = $eventDispatcher;
        $this->dateTime          = $dateTime;
        $this->approveRequestHandler = $approveRequestHandler;
        $this->requestPermissionManager = $requestPermissionManager;
    }

    /**
     * @param CreateRequest $createRequest
     *
     * @return CreateRequestResult|ApproveRequestResult
     */
    public function handle(CreateRequest $createRequest)
    {
        // Create new request
        $request = new Request(
            $createRequest->from,
            $createRequest->participants,
            $createRequest->to,
            [],
            $this->dateTime,
            $createRequest->creator,
            $createRequest->event,
            false,
            null !== $createRequest->description,
            $createRequest->fromPriority,
            false
        );

        $this->requestRepository->add($request);

        // Add message
        if ($createRequest->description) {
            $this->messageRepository->add(new Message(
                $request,
                $request->getFromSheet(),
                $createRequest->description,
                $this->dateTime
            ));
        }

        $this->eventDispatcher->dispatch(
            Events::MEETING_REQUEST_CREATED,
            new CreateRequestEvent($request)
        );

        if ($this->requestPermissionManager->isAllowedToApprove(
            $request,
            $createRequest->from
        )) {
            return $this->approveRequestHandler->handle(new ApproveRequest($createRequest->creator, $request, $createRequest->from, $createRequest->locale));
        }

        return new CreateRequestResult($request);
    }
}
