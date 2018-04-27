<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant\Import;

use Proximum\Vimeet\Domain\Model\Type;

class ImportMappingViewQuery
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var Type
     */
    public $type;

    /**
     * ImportMappingViewQuery constructor.
     *
     * @param Type   $type
     * @param string $locale
     */
    public function __construct(Type $type, $locale)
    {
        $this->locale = $locale;
        $this->type   = $type;
    }
}
