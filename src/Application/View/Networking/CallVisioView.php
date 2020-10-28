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

    /** @var string|null */
    public $header;

    /** @var string|null */
    public $endSound;

    /** @var string|null */
    public $endImage;

    /** @var string|null */
    public $endMessage;


    public function __construct(
        string $token,
        string $sessionId,
        string $apiKey,
        int $timeRemainingInSeconds,
        int $warningTimeRemainingInSeconds,
        ?string $header,
        ?string $endSound,
        ?string $endImage,
        ?string $endMessage
    )
    {
        $this->token = $token;
        $this->sessionId = $sessionId;
        $this->apiKey = $apiKey;
        $this->timeRemainingInSeconds = $timeRemainingInSeconds;
        $this->warningTimeRemainingInSeconds = $warningTimeRemainingInSeconds;
        $this->header = $header;
        $this->endSound = $endSound;
        $this->endImage = $endImage;
        $this->endMessage = $endMessage;
    }

    public function hasHeader(): bool
    {
        return null !== $this->header;
    }

    public function hasEndSound(): bool
    {
        return null !== $this->endSound;
    }

    public function hasEndMessage(): bool
    {
        return null !== $this->endMessage;
    }

    public function hasEndImage(): bool
    {
        return null !== $this->endImage;
    }

    public function hasEndMessageOrImage(): bool
    {
        return null !== $this->endImage
            || null !== $this->endMessage
            ;
    }
}
