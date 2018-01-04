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
    /**
     * @return array
     */
    public static function supportedMimeType()
    {
        return [
            "image/jpeg",
            "image/pjpeg",
            "image/png",
            "image/x-png",
            'image/svg+xml',
        ];
    }
}
