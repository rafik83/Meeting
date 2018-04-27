<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Domain\Model\Happening;

class HappeningViewQuery
{
    /**
     * @var Happening
     */
    public $happening;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Happening $happening
     * @param string    $locale
     */
    public function __construct(Happening $happening, $locale)
    {
        $this->happening = $happening;
        $this->locale    = $locale;
    }
}
