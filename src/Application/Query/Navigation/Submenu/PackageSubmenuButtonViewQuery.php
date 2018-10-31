<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\StaticFormulation;

class PackageSubmenuButtonViewQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $route;

    /** @var null|StaticFormulation */
    public $staticFormulation;

    /** @var string */
    public $locale;

    /**
     * @param Sheet                  $sheet
     * @param string                 $route
     * @param string                 $locale
     * @param null|StaticFormulation $staticFormulation
     */
    public function __construct(
        Sheet $sheet,
        $route,
        string $locale,
        ?StaticFormulation $staticFormulation = null
    ) {
        $this->sheet = $sheet;
        $this->route = $route;
        $this->staticFormulation = $staticFormulation;
        $this->locale = $locale;
    }
}
