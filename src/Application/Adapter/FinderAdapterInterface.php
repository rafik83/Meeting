<?php

namespace Proximum\Vimeet\Application\Adapter;

interface FinderAdapterInterface
{
    public function filesIn(string $path): array;
}
