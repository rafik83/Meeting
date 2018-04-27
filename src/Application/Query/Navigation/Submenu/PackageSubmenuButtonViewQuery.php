<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Domain\Model\Sheet;

class PackageSubmenuButtonViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $route;

    /**
     * @param Sheet  $sheet
     * @param string $route
     */
    public function __construct(Sheet $sheet, $route)
    {
        $this->sheet = $sheet;
        $this->route = $route;
    }
}
