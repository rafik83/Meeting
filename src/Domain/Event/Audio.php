<?php

namespace Proximum\Vimeet\Domain\Event;

final class Audio
{
    public const SUPPORTED_MIME_TYPE = [
        'audio/wav',
        'audio/x-wav',
        'audio/webm',
        'audio/mpeg',
        'audio/mp3',
    ];
}
