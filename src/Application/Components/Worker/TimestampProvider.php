<?php

namespace Proximum\Vimeet\Application\Components\Worker;

/**
 * Give real time timestamp
 * Unlike an http request, a worker cannot keep a unique datetime (generated at start time)
 * while processing different messages
 */
class TimestampProvider
{
    public function getTimestamp(): int
    {
        return time();
    }
}
