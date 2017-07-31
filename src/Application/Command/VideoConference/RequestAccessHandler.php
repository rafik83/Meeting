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
use Proximum\Vimeet\Domain\Model\VideoConferenceToken;
use Proximum\Vimeet\Domain\Repository\VideoConferenceRepositoryInterface;

class RequestAccessHandler
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
     * RequestAccessHandler constructor.
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
     * @param RequestAccess $connectSession
     *
     * @return VideoConferenceView
     */
    public function handle(RequestAccess $connectSession): VideoConferenceView
    {
        $videoConference = $this->videoConferenceRepository->findByMeeting($connectSession->meeting);

        if ($videoConference !== null) {
            $videoConferenceToken = $videoConference->getTokenByUser($connectSession->user);

            if ($videoConferenceToken === null) {
                $token = $this->videoConference->generateAccessToken(
                    $this->videoConference->getSession($videoConference->getSessionId()),
                    $connectSession->meeting->getSlot()
                );

                $videoConference->setToken(
                    new VideoConferenceToken(
                        $videoConference,
                        $connectSession->user,
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
                $this->videoConference->getApiKey()
            );
        }

        $session = $this->videoConference->createSession();
        $token   = $this->videoConference->generateAccessToken($session, $connectSession->meeting->getSlot());

        $this->videoConferenceRepository->add(
            new VideoConference($session->getSessionId(), $connectSession->meeting)
        );

        return new VideoConferenceView(
            $token,
            $session->getSessionId(),
            $this->videoConference->getApiKey()
        );
    }
}
