<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

use Proximum\Vimeet\Application\Components\Sheet\Order\Exception\VatNotEnabledException;
use Proximum\Vimeet\Domain\Model\Event;

abstract class Groups
{
    /**
     * @var GroupView[]
     */
    private $groups;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var float
     */
    private $vat;

    /**
     * Groups constructor.
     *
     * @param GroupView[] $groups
     * @param string      $mode
     * @param float       $vat
     */
    public function __construct(array $groups, $mode, $vat)
    {
        $this->groups = $groups;
        $this->mode   = $mode;
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
        return $this->groups[$name];
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
     * @throws VatNotEnabledException
     */
    public function getTotalWithVat()
    {
        if ($this->mode !== Event::MODE_WITH_VAT) {
            throw new VatNotEnabledException('Vat not enabled');
        }

        return $this->getTotal() * (1 + $this->getVat() / 100);
    }
}
