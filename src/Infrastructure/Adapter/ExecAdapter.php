<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\ExecInterface;

class ExecAdapter implements ExecInterface
{
    /**
     * @param string     $command
     * @param array|null $output
     * @param int|null   $result
     */
    public function exec(string $command, array &$output = null, int &$result = null): void
    {
        exec($command, $output, $result);
    }
}
