<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use OpenTok\OpenTok;
use OpenTok\Session;
use Proximum\Vimeet\Application\Adapter\VideoConferenceInterface;

class VideoConferenceAdapter implements VideoConferenceInterface
{
    /**
     * @var OpenTok
     */
    private $openTok;

    /**
     * VideoConferenceAdapter constructor.
     *
     * @param string $apiKey
     * @param string $apiSecret
     */
    public function __construct(string $apiKey, string $apiSecret)
    {
        $this->openTok = new OpenTok($apiKey, $apiSecret);
    }

    /**
     * @param array $options
     *
     * @return Session
     */
    public function createSession(array $options = []): Session
    {
        return $this->openTok->createSession($options);
    }

    /**
     * @param Session $session
     * @param array   $options
     *
     * @return string
     */
    public function generateAccessToken(Session $session, array $options = []): string
    {
        return $this->openTok->generateToken($session->getSessionId(),$options);
    }
}
