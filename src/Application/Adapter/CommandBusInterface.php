<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
