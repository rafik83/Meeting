<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Event\Meeting\RequestSentEvent;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateRequestHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * CreateRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param EventDispatcherInterface   $eventDispatcher
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->requestRepository = $requestRepository;
        $this->eventDispatcher   = $eventDispatcher;
    }

    /**
     * @param CreateRequest $createRequest
     */
    public function handle(CreateRequest $createRequest)
    {
        // Create new request
        $request = new Request(
            $createRequest->from,
            $createRequest->fromParticipants,
            $createRequest->to,
            [],
            $createRequest->description,
            $createRequest->createdAt,
            $createRequest->creator
        );

        $this->requestRepository->add($request);

        // Dispatch event
        $this->eventDispatcher->dispatch('meeting_request.send', new RequestSentEvent(
            $createRequest->creator,
            $request,
            $createRequest->createdAt,
            $createRequest->description
        ));
    }
}
