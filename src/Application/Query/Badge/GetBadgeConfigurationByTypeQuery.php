<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Badge;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Type;

class GetBadgeConfigurationByTypeQuery implements Query
{
    /** @var Type */
    public $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }
}
