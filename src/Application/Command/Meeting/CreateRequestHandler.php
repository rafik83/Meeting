<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\MeetingRequest\CreateRequestEvent;
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
     * CreateRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param DelayedEventDispatcher     $eventDispatcher
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->requestRepository = $requestRepository;
        $this->messageRepository = $messageRepository;
        $this->eventDispatcher   = $eventDispatcher;
        $this->dateTime          = $dateTime;
    }

    /**
     * @param CreateRequest $createRequest
     *
     * @return CreateRequestResult
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
            null !== $createRequest->description
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

        return new CreateRequestResult($request);
    }
}
