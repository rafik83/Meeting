<?php

namespace Proximum\Vimeet\Application\Adapter;

interface ExecInterface
{
    public function exec(string $command, array &$output = null, int &$result = null): void;
}
