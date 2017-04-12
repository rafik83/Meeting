<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Domain\Model\Type;

class Index
{
    /**
     * @var Type[]
     */
    public $types;

    /**
     * @param Type[] $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }
}
