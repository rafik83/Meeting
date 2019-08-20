<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Helper;

class NameCleaner
{
    public static function clean(string $name): string
    {
        return ucwords($name, ' -');
    }
}
