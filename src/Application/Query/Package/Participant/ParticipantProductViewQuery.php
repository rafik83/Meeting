<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;

class ParticipantProductViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var null|string */
    public $locale;

    /**
     * @param Sheet       $sheet
     * @param null|string $locale
     */
    public function __construct(Sheet $sheet, ?string $locale = null)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
    }
}
