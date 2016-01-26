<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

class OrderMerge extends Groups
{
    /**
     * @var array
     */
    public $vats;

    /**
     * OrderMerge constructor.
     *
     * @param array|GroupView[] $groups
     * @param array             $vats
     */
    public function __construct($groups, array $vats)
    {
        parent::__construct($groups, false, 0);

        $this->vats = $vats;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxes()
    {
        return array_sum($this->vats);
    }
}
