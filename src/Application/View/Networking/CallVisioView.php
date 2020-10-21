<?php

namespace Proximum\Vimeet\Application\View\Networking;

class CallVisioView
{
    /** @var string */
    public $token;

    /** @var string */
    public $apiKey;

    /** @var string */
    public $sessionId;

    public function __construct(string $token, string $sessionId, string $apiKey)
    {

        $this->token = $token;
        $this->sessionId = $sessionId;
        $this->apiKey = $apiKey;
    }
}
