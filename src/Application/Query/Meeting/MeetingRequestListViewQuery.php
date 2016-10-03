<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingRequestListViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var array
     */
    public $filters;

    /**
     * MeetingRequestListViewQuery constructor.
     *
     * @param Sheet  $sheet
     * @param string $locale
     * @param array  $filters
     */
    public function __construct(Sheet $sheet, $locale, array $filters = [])
    {
        $this->sheet   = $sheet;
        $this->locale  = $locale;
        $this->filters = $filters;
    }
}
