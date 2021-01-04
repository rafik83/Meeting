<?php

namespace Proximum\Vimeet\Application\Adapter;

interface ProcessAdapterInterface
{
    public function exec(string $commandline): bool;
}
