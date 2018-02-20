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
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;

class RequestTestAccessHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param VideoConferenceAdapterInterface $videoConferenceAdapter
     * @param \DateTimeInterface              $dateTime
     */
    public function __construct(VideoConferenceAdapterInterface $videoConferenceAdapter, \DateTimeInterface $dateTime)
    {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->dateTime = $dateTime;
    }

    /**
     * @param RequestTestAccess $requestTestAccess
     *
     * @return VideoConferenceView
     * @throws InvalidTokenGeneratorArgumentsException
     */
    public function handle(RequestTestAccess $requestTestAccess): VideoConferenceView
    {
        $session = $this->videoConferenceAdapter->getSession($requestTestAccess->sessionId);

        $token = $this->videoConferenceAdapter->generateAccessToken(
            $session,
            $this->dateTime
        );

        return new VideoConferenceView(
            $token,
            $session->getSessionId(),
            $this->videoConferenceAdapter->getApiKey()
        );
    }
}
