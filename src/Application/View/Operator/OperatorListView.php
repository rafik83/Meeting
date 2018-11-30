<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Operator;

class OperatorListView
{
    /** @var OperatorView[] */
    public $operatorViews;

    public function __construct(array $operatorViews = [])
    {
        $this->operatorViews = $operatorViews;
    }
}
