<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\RequestSentEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoPreferenceWithParticipantException;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
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
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * CreateRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param EventDispatcherInterface   $eventDispatcher
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        EventDispatcherInterface $eventDispatcher,
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
     * @throws NoPreferenceWithParticipantException
     */
    public function handle(CreateRequest $createRequest)
    {
        $participantFound  = false;
        $noPreferenceFound = false;
        foreach ($createRequest->participants as $participant) {
            if (!$participant instanceof Participant) {
                $noPreferenceFound = true;
            }
            
            if ($participant instanceof  Participant) {
                $participantFound = true;
            }
        }
        
        if (true === $participantFound && true === $noPreferenceFound) {
            throw new NoPreferenceWithParticipantException('You can not have no preference and a participant selected at the same time');
        }

        // Create new request
        $request = new Request(
            $createRequest->from,
            $createRequest->participants,
            $createRequest->to,
            [],
            $this->dateTime,
            $createRequest->creator
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

        // Notify request creation
        $this->eventDispatcher->dispatch(Events::REQUEST_SENT, new RequestSentEvent(
            $createRequest->creator,
            $request,
            $this->dateTime,
            $createRequest->description
        ));

        // Notify participant add
        foreach ($createRequest->participants as $participant) {
            $this->eventDispatcher->dispatch(Events::REQUEST_PARTICIPANT_ADDED, new ParticipantAddedEvent(
                $createRequest->creator,
                $participant,
                $request,
                $createRequest->description,
                $this->dateTime
            ));
        }
    }
}
