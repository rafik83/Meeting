<?php

namespace Proximum\Vimeet\Domain\Happening\Webinar;

final class Stream
{
    public const ACTION_START = 'stream_start';
    public const ACTION_STOP = 'stream_stop';

    public const TYPE_SCREEN = 'screen';
    public const TYPE_VIDEO = 'video';
    public const TYPE_CUSTOM = 'custom'; // May be used to share a video.

    public const STREAM_TYPES = [
        self::TYPE_VIDEO,
        self::TYPE_CUSTOM,
        self::TYPE_SCREEN,
    ];
}
