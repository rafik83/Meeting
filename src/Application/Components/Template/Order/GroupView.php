<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Type\Order;

class GroupView
{
    /**
     * @var RowView[]
     */
    private $rows = [];

    /**
     * @var string
     */
    public $label;

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
     * @return float
     */
    public function getTotal()
    {
        return array_reduce($this->rows, function ($carry, RowView $rowView) {
            return $carry + $rowView->getTotal();
        }, 0);
    }
}
