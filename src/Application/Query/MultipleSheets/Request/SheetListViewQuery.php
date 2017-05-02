<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\MultipleSheets\Request;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetListViewQuery
{
    /** @var Sheet[] indexed by sheet id */
    public $sheets;

    /** @var string */
    public $locale;

    /** @var int */
    public $page;

    /** @var int */
    public $limit;

    /**
     * @param Sheet[] $sheets indexed by sheet id
     * @param string  $locale
     * @param int     $page
     * @param int     $limit
     */
    public function __construct(array $sheets, $locale, $page, $limit)
    {
        $this->sheets = $sheets;
        $this->locale = $locale;
        $this->page = $page;
        $this->limit = $limit;
    }
}
