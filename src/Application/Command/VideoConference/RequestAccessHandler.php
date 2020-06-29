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
use Proximum\Vimeet\Application\Components\Visio\VisioSettingsRetriever;
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\VideoConference;
use Proximum\Vimeet\Domain\Model\VideoConferenceToken;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\VideoConferenceRepositoryInterface;

class RequestAccessHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var VideoConferenceRepositoryInterface */
    private $videoConferenceRepository;

    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var VisioSettingsRetriever */
    private $visioSettingsRetriever;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        VideoConferenceRepositoryInterface $videoConferenceRepository,
        VisioSettingsRetriever $visioSettingsRetriever
    ) {
        $this->meetingRepository = $meetingRepository;
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
        $this->addParticipantToMeeting($requestAccess->participant, $requestAccess->meeting);

        $videoConference = $this->videoConferenceRepository->findByMeeting($requestAccess->meeting);
        $visioSettings = $this->visioSettingsRetriever->get($requestAccess->meeting->getEvent());

        if (null !== $videoConference) {
            $videoConferenceToken = $videoConference->getTokenByUser($requestAccess->user);

            if (null === $videoConferenceToken) {
                $token = $this->videoConferenceAdapter->generateAccessToken(
                    $this->videoConferenceAdapter->getSession($videoConference->getSessionId()),
                    $requestAccess->meeting->getSlot()->getEnd()
                );

                $videoConference->setToken(
                    new VideoConferenceToken(
                        $videoConference,
                        $requestAccess->user,
                        $token
                    )
                );

                $this->videoConferenceRepository->set($videoConference);
            } else {
                $token = $videoConferenceToken->getToken();
            }

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
        $videoConference->setToken(new VideoConferenceToken(
            $videoConference,
            $requestAccess->user,
            $token
        ));

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

    private function addParticipantToMeeting(Participant $participant, Meeting $meeting): void
    {
        if ($meeting->hasParticipant($participant)) {
            return;
        }

        $meeting->addParticipant($participant);
        $this->meetingRepository->set($meeting);
    }
}
