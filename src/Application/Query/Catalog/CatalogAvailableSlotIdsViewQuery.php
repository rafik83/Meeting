<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class CatalogAvailableSlotIdsViewQuery
{
    /** @var User */
    public $user;

    /** @var Sheet */
    public $sheet;

    /** @var Event */
    public $event;

    /** @var array */
    public $filters;

    /**
     * @param Event $event
     * @param Sheet $sheet
     * @param User  $user
     * @param array $filters
     */
    public function __construct(Event $event, Sheet $sheet, User $user, array $filters)
    {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->user = $user;
        $this->filters = $filters;
    }
}
