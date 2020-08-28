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

    public function __construct(TokboxBroadcast $tokboxBroadcast)
    {
        $this->broadcastId = $tokboxBroadcast->id;
        $this->sessionId = $tokboxBroadcast->sessionId;
        $this->isStopped = $tokboxBroadcast->isStopped;
        $this->hls = $tokboxBroadcast->__get('hlsUrl');
        $this->rtmp = $tokboxBroadcast->broadcastUrls['rtmp'] ?? [];
    }

    public function getBroadcastId(): string
    {
        return $this->broadcastId;
    }
}
