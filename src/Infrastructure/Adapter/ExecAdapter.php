<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

class ExecAdapter
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
