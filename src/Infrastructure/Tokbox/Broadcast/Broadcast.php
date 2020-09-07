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

    /** @var string */
    private $hls;

    /** @var array */
    private $rtmp;

    public function __construct(
        $broadcastId,
        $sessionId,
        $isStopped,
        $hls,
        $rtmp
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

    public function getHlsUrl(): ?string
    {
        return $this->hls;
    }
}
