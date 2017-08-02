<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\VideoConference;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
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
     * @var VideoConferenceAdapterInterface
     */
    private $videoConferenceAdapter;

    /**
     * RequestAccessHandler constructor.
     *
     * @param VideoConferenceAdapterInterface    $videoConferenceAdapter
     * @param VideoConferenceRepositoryInterface $videoConferenceRepository
     */
    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        VideoConferenceRepositoryInterface $videoConferenceRepository
    ) {
        $this->videoConferenceRepository = $videoConferenceRepository;
        $this->videoConferenceAdapter    = $videoConferenceAdapter;
    }

    /**
     * @param RequestAccess $requestAccess
     * @throw InvalidTokenGeneratorArgumentsException
     *
     * @return VideoConferenceView
     */
    public function handle(RequestAccess $requestAccess): VideoConferenceView
    {
        $videoConference = $this->videoConferenceRepository->findByMeeting($requestAccess->meeting);

        if ($videoConference !== null) {
            $videoConferenceToken = $videoConference->getTokenByUser($requestAccess->user);

            if ($videoConferenceToken === null) {
                $token = $this->videoConferenceAdapter->generateAccessToken(
                    $this->videoConferenceAdapter->getSession($videoConference->getSessionId()),
                    $requestAccess->meeting->getSlot()
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
                $this->videoConferenceAdapter->getApiKey()
            );
        }

        $session = $this->videoConferenceAdapter->createSession();
        $token   = $this->videoConferenceAdapter->generateAccessToken($session, $requestAccess->meeting->getSlot());

        $this->videoConferenceRepository->add(
            new VideoConference($session->getSessionId(), $requestAccess->meeting)
        );

        return new VideoConferenceView(
            $token,
            $session->getSessionId(),
            $this->videoConferenceAdapter->getApiKey()
        );
    }
}
