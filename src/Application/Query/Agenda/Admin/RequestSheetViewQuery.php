<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;

class RequestSheetViewQuery
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
     * @var Request
     */
    public $request;

    /**
     * RequestSheetViewQuery constructor.
     *
     * @param Sheet   $sheet
     * @param Request $request
     * @param string  $locale
     */
    public function __construct(Sheet $sheet, Request $request, $locale)
    {
        $this->sheet   = $sheet;
        $this->locale  = $locale;
        $this->request = $request;
    }
}
