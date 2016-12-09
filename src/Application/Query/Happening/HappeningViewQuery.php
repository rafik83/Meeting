<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

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
     * @var int
     */
    public $key;

    /**
     * HappeningViewQuery constructor.
     *
     * @param Happening $happening
     * @param string    $locale
     * @param int       $key
     */
    public function __construct(Happening $happening, $locale, $key)
    {
        $this->happening = $happening;
        $this->locale    = $locale;
        $this->key       = $key;
    }
}
