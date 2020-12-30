<?php

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
