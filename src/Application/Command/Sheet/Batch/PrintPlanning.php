<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Batch;

use Proximum\Vimeet\Application\Command\Sheet\AbstractBatch;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class PrintPlanning extends AbstractBatch
{
    /** @var Admin */
    public $admin;

    /** @var string */
    public $orderBy;

    /** @var string */
    public $locale;

    /** @var Event */
    public $event;

    /** @var bool */
    public $withBadge;

    public function __construct(
        Event $event,
        array $sheetIds,
        Admin $admin,
        string $orderBy,
        string $locale,
        bool $withBadge = false
    ) {
        $this->event = $event;
        $this->ids = $sheetIds;
        $this->admin = $admin;
        $this->orderBy = $orderBy;
        $this->locale = $locale;
        $this->withBadge = $withBadge;
    }
}
