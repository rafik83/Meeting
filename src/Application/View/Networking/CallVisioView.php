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

    /** @var int */
    public $timeRemainingInSeconds;

    /** @var int */
    public $warningTimeRemainingInSeconds;


    public function __construct(string $token, string $sessionId, string $apiKey, int $timeRemainingInSeconds, int $warningTimeRemainingInSeconds)
    {
        $this->token = $token;
        $this->sessionId = $sessionId;
        $this->apiKey = $apiKey;
        $this->timeRemainingInSeconds = $timeRemainingInSeconds;
        $this->warningTimeRemainingInSeconds = $warningTimeRemainingInSeconds;

    }
}
