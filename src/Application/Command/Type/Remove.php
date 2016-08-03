<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Domain\Model\Type;

class Remove
{
    /**
     * @var Type
     */
    public $type;

    /**
     * Remove constructor.
     *
     * @param Type $type
     */
    public function __construct(Type $type)
    {
        $this->type = $type;}
}
