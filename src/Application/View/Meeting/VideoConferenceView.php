<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
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
     * VideoConferenceView constructor.
     *
     * @param string $token
     * @param string $apiKey
     */
    public function __construct(string $token, string $apiKey)
    {
        $this->token  = $token;
        $this->apiKey = $apiKey;
    }
}
