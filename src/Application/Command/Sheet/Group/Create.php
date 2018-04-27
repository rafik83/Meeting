<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Group;

use Proximum\Vimeet\Application\View\Group\Sheet\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class Create
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var SheetView[] */
    public $sheetViews;

    /** @var string */
    public $title;

    /**
     * Create constructor.
     *
     * @param Event $event
     * @param User  $user
     */
    public function __construct(Event $event, User $user)
    {
        $this->event      = $event;
        $this->user       = $user;
        $this->sheetViews = [];
    }
}
