<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event;

class Image
{
    const SUPPORTED_MIME_TYPE = [
        "image/jpeg",
        "image/pjpeg",
        "image/png",
        "image/x-png",
        'image/svg+xml',
    ];
}
