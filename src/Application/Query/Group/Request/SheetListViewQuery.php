<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Group\Request;

use Proximum\Vimeet\Domain\Model\Sheet\Group;

class SheetListViewQuery
{
    /** @var Group */
    public $group;

    /** @var string */
    public $locale;

    /** @var int */
    public $page;

    /** @var int */
    public $limit;

    /**
     * @param Group  $group
     * @param string $locale
     * @param int    $page
     * @param int    $limit
     */
    public function __construct(Group $group, $locale, $page, $limit)
    {
        $this->group = $group;
        $this->locale = $locale;
        $this->page = $page;
        $this->limit = $limit;
    }
}
