<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class PaginatedCatalogSheetPreviewViewQuery
{
    /** @var Event */
    public $event;

    /** @var array */
    public $filters;

    /** @var int */
    public $page;

    /** @var int */
    public $limit;

    /** @var string */
    public $locale;

    /** @var Sheet */
    public $viewer;

    /** @var User */
    public $user;

    /** @var array */
    public $availableSlotIds;

    /** @var array */
    public $sheetsToExclude;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     * @param Sheet  $viewer
     * @param User   $user
     * @param array  $availableSlotIds
     * @param array  $sheetsToExclude
     */
    public function __construct(
        Event $event,
        array $filters,
        $page,
        $limit,
        $locale,
        Sheet $viewer,
        User $user,
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ) {
        $this->event            = $event;
        $this->filters          = $filters;
        $this->page             = $page;
        $this->limit            = $limit;
        $this->locale           = $locale;
        $this->viewer           = $viewer;
        $this->user             = $user;
        $this->availableSlotIds = $availableSlotIds;
        $this->sheetsToExclude  = $sheetsToExclude;
    }
}
