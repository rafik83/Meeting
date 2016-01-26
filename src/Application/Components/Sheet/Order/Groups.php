<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

abstract class Groups
{
    /**
     * @var GroupView[]
     */
    private $groups;

    /**
     * @var float
     */
    private $vat;

    /**
     * Groups constructor.
     *
     * @param GroupView[] $groups
     * @param float       $vat
     */
    public function __construct(array $groups, $vat)
    {
        $this->groups = $groups;
        $this->vat    = $vat;
    }

    /**
     * Get groups
     *
     * @return GroupView[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

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
     * @param string $name
     *
     * @return GroupView
     */
    public function getGroup($name)
    {
        return isset($this->groups[$name]) ? $this->groups[$name] : null;
    }

    /**
     * Get vat
     *
     * @return float
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * Set vat
     *
     * @param float $vat
     *
     * @return OrderView
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

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

    /**
     * @return float
     */
    public function getTotalWithVat()
    {
        return $this->getTotal() * (1 + $this->getVat() / 100);
    }
}
