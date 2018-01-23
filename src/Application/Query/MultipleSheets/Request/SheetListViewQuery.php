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
use Proximum\Vimeet\Domain\Model\User;

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

    /** @var FilterRequestView */
    public $filterRequestView;

    /** @var User */
    public $user;

    /**
     * @param User              $user
     * @param Sheet[]           $sheets indexed by sheet id
     * @param string            $locale
     * @param int               $page
     * @param int               $limit
     * @param FilterRequestView $filterRequestView
     */
    public function __construct(User $user, array $sheets, $locale, $page, $limit, FilterRequestView $filterRequestView)
    {
        $this->sheets            = $sheets;
        $this->locale            = $locale;
        $this->page              = $page;
        $this->limit             = $limit;
        $this->filterRequestView = $filterRequestView;
        $this->user              = $user;
    }
}
