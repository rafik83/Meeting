<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Type\Order;

class OrderView
{
    /**
     * @var GroupView[]
     */
    private $groups = [];

    /**
     * @param string    $name
     * @param GroupView $groupView
     *
     * @return $this
     */
    public function addGroupView($name, GroupView $groupView)
    {
        $this->groups[$name] = $groupView;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return array_reduce($this->groups, function ($carry, GroupView $groupView) {
            return $carry + $groupView->getTotal();
        }, 0);
    }
}
