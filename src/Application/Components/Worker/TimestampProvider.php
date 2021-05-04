<?php

namespace Proximum\Vimeet\Application\Components\Worker;

/**
 * Give real time timestamp, usefull for long running jobs (dateTime service provides an immutable date)
 */
class TimestampProvider
{
    public function getTimestamp(): int
    {
        return time();
    }
}
