<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Behat\Transliterator\Transliterator;
use Proximum\Vimeet\Application\Adapter\TransliteratorAdapterInterface;

class TransliteratorAdapter implements TransliteratorAdapterInterface
{
    public function urlize(array $parameters): string
    {
        return Transliterator::urlize(implode('-', $parameters));
    }
}
