<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event;

class Image
{
    public const SUPPORTED_MIME_TYPE = [
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/x-png',
        'image/svg+xml',
        'image/gif',
    ];
}
