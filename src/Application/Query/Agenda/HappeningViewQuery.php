<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

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
     * @var string
     */
    public $key;

    /**
     * @param Happening $happening
     * @param string    $locale
     * @param string    $key
     */
    public function __construct(Happening $happening, $locale, $key)
    {
        $this->happening = $happening;
        $this->locale    = $locale;
        $this->key       = $key;
    }
}
