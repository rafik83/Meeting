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
    public $groups;

    /**
     * @var bool
     */
    public $vatApplicable;

    /**
     * @var float
     */
    public $vat;

    /**
     * Groups constructor.
     *
     * @param GroupView[] $groups
     * @param bool        $vatApplicable
     * @param float       $vat
     */
    public function __construct(array $groups, $vatApplicable, $vat)
    {
        $this->groups        = $groups;
        $this->vatApplicable = $vatApplicable;
        $this->vat           = $vat;
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
        return $this->groups[$name];
    }

    /**
     * @return float
     */
    public function getTotalWithoutTaxes()
    {
        return array_reduce($this->groups, function ($carry, GroupView $groupView) {
            return $carry + $groupView->getTotal();
        }, 0);
    }

    /**
     * @return float
     */
    public function getTotalWithTaxes()
    {
        return $this->getTotalWithoutTaxes() * (1 + $this->vat / 100);
    }

    /**
     * @deprecated
     */
    public function getTotalWithVat()
    {
        return $this->getTotalWithoutTaxes();
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->vatApplicable ? $this->getTotalWithTaxes() : $this->getTotalWithoutTaxes();
    }
}
