<?php

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
        ?string $header = null,
        ?string $endSound = null,
        ?string $endImage = null,
        ?string $endMessage = null
    ) {
        $this->token = $token;
        $this->apiKey = $apiKey;
        $this->sessionId = $sessionId;
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
