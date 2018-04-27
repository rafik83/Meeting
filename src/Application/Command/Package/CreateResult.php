<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Domain\Model\Package;

class CreateResult
{
    /**
     * @var Package
     */
    public $package;

    /**
     * CreateResult constructor.
     *
     * @param Package $package
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }
}
