<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\VideoConference;

use Proximum\Vimeet\Application\Adapter\VideoConferenceInterface;
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;
use Proximum\Vimeet\Domain\Model\VideoConference;
use Proximum\Vimeet\Domain\Repository\VideoConferenceRepositoryInterface;

class ConnectSessionHandler
{
    /**
     * @var VideoConferenceRepositoryInterface
     */
    private $videoConferenceRepository;

    /**
     * @var VideoConferenceInterface
     */
    private $videoConference;

    /**
     * ConnectSessionHandler constructor.
     *
     * @param VideoConferenceInterface           $videoConference
     * @param VideoConferenceRepositoryInterface $videoConferenceRepository
     */
    public function __construct(
        VideoConferenceInterface $videoConference,
        VideoConferenceRepositoryInterface $videoConferenceRepository
    ) {
        $this->videoConferenceRepository = $videoConferenceRepository;
        $this->videoConference           = $videoConference;
    }

    /**
     * @param ConnectSession $connectSession
     *
     * @return VideoConferenceView
     */
    public function handle(ConnectSession $connectSession): VideoConferenceView
    {
        $videoConference = $this->videoConferenceRepository->findByMeeting($connectSession->meeting);

        if ($videoConference !== null) {
            $token = $videoConference->getTokenByUser($connectSession->user);

            if ($token === null) {
                $token = $this->videoConference->generateAccessToken(
                    $this->videoConference->getSession($videoConference->getSessionId())
                );
            }

            return new VideoConferenceView(
                $token,
                $videoConference->getSessionId(),
                $this->videoConference->getApiKey()
            );
        }

        $session = $this->videoConference->createSession();
        $token   = $this->videoConference->generateAccessToken($session);

        $this->videoConferenceRepository->set(
            new VideoConference($session->getSessionId(), $connectSession->meeting)
        );

        return new VideoConferenceView(
            $token,
            $session->getSessionId(),
            $this->videoConference->getApiKey()
        );
    }
}
