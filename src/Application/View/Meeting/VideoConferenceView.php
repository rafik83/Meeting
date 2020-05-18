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
    /** @var string */
    public $token;

    /** @var string */
    public $apiKey;

    /** @var string */
    public $sessionId;

    /** @var string|null */
    public $header;

    public function __construct(
        string $token,
        string $sessionId,
        string $apiKey,
        ?string $header = null
    ) {
        $this->token = $token;
        $this->apiKey = $apiKey;
        $this->sessionId = $sessionId;
        $this->header = $header;
    }

    public function hasHeader(): bool
    {
        return null !== $this->header;
    }
}
