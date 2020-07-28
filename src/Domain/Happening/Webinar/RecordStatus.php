<?php

namespace Proximum\Vimeet\Domain\Happening\Webinar;

final class RecordStatus
{
    public const STARTED = 'started';
    public const STOPPED = 'stopped';
    public const PAUSED = 'paused';

    public const IS_RECORDING_STATUS = [
        self::STARTED,
        self::PAUSED,
    ];
}
