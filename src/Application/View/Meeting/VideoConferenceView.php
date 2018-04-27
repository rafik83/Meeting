<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting;

class VideoConferenceView
{
    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $apiKey;

    /**
     * @var string
     */
    public $sessionId;

    /**
     * VideoConferenceView constructor.
     *
     * @param string $token
     * @param string $sessionId
     * @param string $apiKey
     */
    public function __construct(string $token, string $sessionId, string $apiKey)
    {
        $this->token     = $token;
        $this->apiKey    = $apiKey;
        $this->sessionId = $sessionId;
    }
}
