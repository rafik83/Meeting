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

class RequestTestAccessHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var VisioSettingsRetriever */
    private $visioSettingsRetriever;

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        VisioSettingsRetriever $visioSettingsRetriever,
        \DateTimeInterface $dateTime
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->dateTime = $dateTime;
        $this->visioSettingsRetriever = $visioSettingsRetriever;
    }

    /**
     * @param RequestTestAccess $requestTestAccess
     *
     * @throws InvalidTokenGeneratorArgumentsException
     *
     * @return VideoConferenceView
     */
    public function handle(RequestTestAccess $requestTestAccess): VideoConferenceView
    {
        $session = $this->videoConferenceAdapter->getSession($requestTestAccess->sessionId);
        $visioSettings = $this->visioSettingsRetriever->get($requestTestAccess->event);

        $token = $this->videoConferenceAdapter->generateAccessToken(
            $session,
            $this->dateTime
        );

        return new VideoConferenceView(
            $token,
            $session->getSessionId(),
            $this->videoConferenceAdapter->getApiKey(),
            $visioSettings->getHeader($requestTestAccess->locale),
            $visioSettings->getEndSound($requestTestAccess->locale),
            $visioSettings->getEndImage($requestTestAccess->locale),
            $visioSettings->getEndMessage($requestTestAccess->locale)
        );
    }
}
