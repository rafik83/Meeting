<?php

namespace Proximum\Vimeet\Application\Adapter;

interface TransliteratorAdapterInterface
{
    public function urlize(array $parameters): string;
}
