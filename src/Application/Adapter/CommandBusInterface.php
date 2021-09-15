<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Application\Command\Command;

interface CommandBusInterface
{
    /**
     * @param Command $command
     *
     * @return mixed
     */
    public function handle(Command $command);
}
