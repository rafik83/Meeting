<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\VideoConference;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Command\Meeting\AddParticipantToMeeting;
use Proximum\Vimeet\Application\Command\Meeting\AddParticipantToMeetingHandler;
use Proximum\Vimeet\Application\Components\Visio\VisioSettingsRetriever;
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;
use Proximum\Vimeet\Domain\Model\VideoConference;
use Proximum\Vimeet\Domain\Model\VideoConferenceToken;
use Proximum\Vimeet\Domain\Repository\VideoConferenceRepositoryInterface;

class RequestAccessHandler
{
    /** @var AddParticipantToMeetingHandler */
    private $addParticipantToMeetingHandler;

    /** @var VideoConferenceRepositoryInterface */
    private $videoConferenceRepository;

    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var VisioSettingsRetriever */
    private $visioSettingsRetriever;

    public function __construct(
        AddParticipantToMeetingHandler $addParticipantToMeetingHandler,
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        VideoConferenceRepositoryInterface $videoConferenceRepository,
        VisioSettingsRetriever $visioSettingsRetriever
    ) {
        $this->addParticipantToMeetingHandler = $addParticipantToMeetingHandler;
        $this->videoConferenceRepository = $videoConferenceRepository;
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->visioSettingsRetriever = $visioSettingsRetriever;
    }

    /**
     * @param RequestAccess $requestAccess
     *
     * @throws InvalidTokenGeneratorArgumentsException
     *
     * @return VideoConferenceView
     */
    public function handle(RequestAccess $requestAccess): VideoConferenceView
    {
        $this->addParticipantToMeetingHandler->handle(
            new AddParticipantToMeeting($requestAccess->participant, $requestAccess->meeting)
        );

        $videoConference = $this->videoConferenceRepository->findByMeeting($requestAccess->meeting);
        $visioSettings = $this->visioSettingsRetriever->get($requestAccess->meeting->getEvent());

        if (null !== $videoConference) {
            $token = $this->videoConferenceAdapter->generateAccessToken(
                $this->videoConferenceAdapter->getSession($videoConference->getSessionId()),
                $requestAccess->meeting->getSlot()->getEnd()
            );

            return new VideoConferenceView(
                $token,
                $videoConference->getSessionId(),
                $this->videoConferenceAdapter->getApiKey(),
                $visioSettings->getHeader($requestAccess->locale),
                $visioSettings->getEndSound($requestAccess->locale),
                $visioSettings->getEndImage($requestAccess->locale),
                $visioSettings->getEndMessage($requestAccess->locale)
            );
        }

        $session = $this->videoConferenceAdapter->createSession();
        $token = $this->videoConferenceAdapter->generateAccessToken(
            $session,
            $requestAccess->meeting->getSlot()->getEnd()
        );

        $videoConference = new VideoConference($session->getSessionId(), $requestAccess->meeting);

        $this->videoConferenceRepository->add($videoConference);

        return new VideoConferenceView(
            $token,
            $session->getSessionId(),
            $this->videoConferenceAdapter->getApiKey(),
            $visioSettings->getHeader($requestAccess->locale),
            $visioSettings->getEndSound($requestAccess->locale),
            $visioSettings->getEndImage($requestAccess->locale),
            $visioSettings->getEndMessage($requestAccess->locale)
        );
    }
}
