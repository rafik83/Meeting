<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Export;

class CustomRowViewQuery
{
    /** @var int */
    public $customRowIndex;

    /** @var string */
    public $adminLocale;

    /**
     * @param int    $customRowIndex
     * @param string $adminLocale
     */
    public function __construct($customRowIndex, $adminLocale)
    {
        $this->customRowIndex = $customRowIndex;
        $this->adminLocale    = $adminLocale;
    }
}
