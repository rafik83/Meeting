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
use Proximum\Vimeet\Application\Event\Meeting\RequestCanceledEvent;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CancelRequestHandler
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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * CancelRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param EventDispatcherInterface   $eventDispatcher
     * @param DateTimeInterface          $createdAt
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        EventDispatcherInterface $eventDispatcher,
        DateTimeInterface $createdAt
    ) {
        $this->requestRepository = $requestRepository;
        $this->messageRepository = $messageRepository;
        $this->eventDispatcher   = $eventDispatcher;
        $this->createdAt         = $createdAt;
    }

    /**
     * @param CancelRequest $cancelRequest
     */
    public function handle(CancelRequest $cancelRequest)
    {
        // Cancel request
        $this->requestRepository->set($cancelRequest->request->cancel($cancelRequest->date));

        // Add message
        if ($cancelRequest->message) {
            $this->messageRepository->add(new Message(
                $cancelRequest->request,
                $cancelRequest->request->getFromSheet(), // @todo Fix in case of toSheet
                $cancelRequest->message,
                $this->createdAt
            ));
        }

        // Dispatch event
        $this->eventDispatcher->dispatch(
            'meeting_request.canceled',
            new RequestCanceledEvent(
                $cancelRequest->emitter,
                $cancelRequest->request,
                $this->createdAt,
                $cancelRequest->message
            )
        );
    }
}
