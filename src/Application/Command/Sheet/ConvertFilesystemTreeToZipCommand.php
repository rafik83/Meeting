<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;

class ConvertFilesystemTreeToZipCommand implements Command
{
    /** @var string */
    public $rootDir;

    /** @var null|string */
    public $password;

    public function __construct(string $rootDir, ?string $password = null)
    {
        $this->rootDir = $rootDir;
        $this->password = $password;
    }
}
