<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar;

class SessionAndTokenView
{
    /** @var string */
    public $sessionId;

    /** @var string */
    public $token;

    /** @var string */
    public $apiKey;

    public function __construct(string $sessionId = '', string $token = '', string $apiKey = '')
    {
        $this->sessionId = $sessionId;
        $this->token = $token;
        $this->apiKey = $apiKey;
    }
}
