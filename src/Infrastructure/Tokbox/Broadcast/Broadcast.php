<?php

namespace Proximum\Vimeet\Infrastructure\Tokbox\Broadcast;

use OpenTok\Broadcast as TokboxBroadcast;
use Proximum\Vimeet\Domain\Happening\Webinar\Broadcast\Broadcast as DomainBroadcast;

class Broadcast implements DomainBroadcast
{
    /** @var string */
    private $broadcastId;

    /** @var string */
    private $sessionId;

    /** @var bool */
    private $isStopped;

    /** @var string|null */
    private $hls;

    /** @var array|null */
    private $rtmp;

    public function __construct(
        string $broadcastId,
        string $sessionId,
        bool $isStopped,
        ?string $hls,
        ?array $rtmp
    ) {
        $this->broadcastId = $broadcastId;
        $this->sessionId = $sessionId;
        $this->isStopped = $isStopped;
        $this->hls = $hls;
        $this->rtmp = $rtmp;
    }

    public static function createFromTokboxObject(TokboxBroadcast $tokboxBroadcast): self
    {
        return new self(
            $tokboxBroadcast->id,
            $tokboxBroadcast->sessionId,
            $tokboxBroadcast->isStopped,
            $tokboxBroadcast->__get('hlsUrl'),
            $tokboxBroadcast->broadcastUrls['rtmp'] ?? []
        );
    }

    public static function createFromJson($broadcastJson): self
    {
        return new self(
            $broadcastJson['id'],
            $broadcastJson['sessionId'],
            $broadcastJson['status'] !== 'started',
            $broadcastJson['broadcastUrls']['hls'] ?? null,
            $broadcastJson['broadcastUrls']['rtmp'] ?? []
        );
    }

    public function getBroadcastId(): string
    {
        return $this->broadcastId;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getHlsUrl(): ?string
    {
        return $this->hls;
    }
}
