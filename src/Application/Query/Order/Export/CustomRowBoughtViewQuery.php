<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Domain\Model\Order\Row;

class CustomRowBoughtViewQuery
{
    /** @var Row */
    public $row;

    /** @var string */
    public $adminLocale;

    /**
     * @param Row    $row
     * @param string $adminLocale
     */
    public function __construct(Row $row, $adminLocale)
    {
        $this->row         = $row;
        $this->adminLocale = $adminLocale;
    }
}
