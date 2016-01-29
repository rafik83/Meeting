<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

class GroupView
{
    /**
     * @var RowView[]
     */
    private $rows = [];

    /**
     * @var string
     */
    private $label;

    /**
     * GroupView constructor.
     *
     * @param string $label
     */
    public function __construct($label)
    {
        $this->label = $label;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string  $name
     * @param RowView $rowView
     *
     * @return $this
     */
    public function addRowView($name, RowView $rowView)
    {
        $this->rows[$name] = $rowView;

        return $this;
    }

    /**
     * Get rows
     *
     * @return RowView[]
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Has rows
     *
     * @return bool
     */
    public function hasRows()
    {
        return !empty($this->rows);
    }

    /**
     * @param string $name
     *
     * @return RowView
     */
    public function getRow($name)
    {
        return $this->rows[$name];
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return array_reduce($this->rows, function ($carry, RowView $rowView) {
            return $carry + $rowView->getTotal();
        }, 0);
    }
}
