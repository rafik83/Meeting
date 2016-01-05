<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use DateTimeInterface;
use Proximum\Vimeet\Application\Event\Meeting\RequestRefusedEvent;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RefuseRequestHandler
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
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * RefuseRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param EventDispatcherInterface   $eventDispatcher
     * @param DateTimeInterface          $createdAt
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        EventDispatcherInterface $eventDispatcher,
        DateTimeInterface $createdAt
    ) {
        $this->requestRepository = $requestRepository;
        $this->eventDispatcher   = $eventDispatcher;
        $this->createdAt         = $createdAt;
    }

    /**
     * @param RefuseRequest $refuseRequest
     */
    public function handle(RefuseRequest $refuseRequest)
    {
        $this->requestRepository->set($refuseRequest->request->refuse());
        $this->eventDispatcher->dispatch('meeting_request.refused', new RequestRefusedEvent($refuseRequest->emitter, $refuseRequest->request, $this->createdAt, $refuseRequest->message));
    }
}
