<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Rule;

use Proximum\Vimeet\Domain\Model\Rule;

class SeeWhat
{
    /**
     * @var Rule
     */
    public $rule;

    /**
     * @var array
     */
    public $seeWhat;

    /**
     * @param Rule $rule
     */
    public function __construct(Rule $rule)
    {
        $this->rule = $rule;
    }
}
