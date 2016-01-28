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
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipantAddedEvent;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * CreateRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param EventDispatcherInterface   $eventDispatcher
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->requestRepository = $requestRepository;
        $this->messageRepository = $messageRepository;
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
            $createRequest->createdAt,
            $createRequest->creator
        );

        $this->requestRepository->add($request);

        // Add message
        $this->messageRepository->add(new Message(
            $request,
            $request->getFromSheet(),
            $createRequest->description,
            $createRequest->createdAt
        ));

        // Notify request creation
        $this->eventDispatcher->dispatch('meeting_request.send', new RequestSentEvent(
            $createRequest->creator,
            $request,
            $createRequest->createdAt,
            $createRequest->description
        ));

        // Notify participant add
        $participants = array_merge($createRequest->fromParticipants, $createRequest->toParticipants);
        foreach ($participants as $participant) {
            $this->eventDispatcher->dispatch('meeting_request.participant.added', new ParticipantAddedEvent(
                $createRequest->creator,
                $participant,
                $request,
                $createRequest->description,
                $createRequest->createdAt
            ));
        }
    }
}
