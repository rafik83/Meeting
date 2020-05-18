<?php

namespace Proximum\Vimeet\Domain\Event;

final class Audio
{
    public const SUPPORTED_MIME_TYPE = [
        'audio/webm',
        'audio/x-wav',
        'audio/wav',
        'audio/aac',
        'audio/mpeg',
        'audio/mp3',
    ];
}
