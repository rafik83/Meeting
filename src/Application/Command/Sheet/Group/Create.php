<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
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
    public $sheetViews = [];

    /** @var string */
    public $title;

    /** @var \DateTimeInterface */
    public $dateTime;

    /**
     * Create constructor.
     *
     * @param Event           $event
     * @param User            $user
     * @param SheetView[]     $sheetViews
     */
    public function __construct(Event $event, User $user, array $sheetViews)
    {
        $this->event      = $event;
        $this->user       = $user;
        $this->sheetViews = $sheetViews;
        $this->dateTime   = new \DateTime();
    }
}
