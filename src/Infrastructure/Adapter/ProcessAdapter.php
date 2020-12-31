<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\ProcessAdapterInterface;
use Symfony\Component\Process\Process;

class ProcessAdapter implements ProcessAdapterInterface
{
    public function exec(string $commandline): bool
    {
        try {
            $process = new Process($commandline);
            $process->run();

            return $process->isSuccessful();
        } catch (\RuntimeException $e) {
            return false;
        }
    }
}
