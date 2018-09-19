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

class PrintPlanningAndBadge extends AbstractBatch
{
    /** @var Admin */
    public $admin;

    /** @var string */
    public $orderBy;

    /** @var string */
    public $locale;

    /** @var Event */
    public $event;

    public function __construct(
        Event $event,
        array $sheetIds,
        Admin $admin,
        string $orderBy,
        string $locale
    ) {
        $this->event = $event;
        $this->ids = $sheetIds;
        $this->admin = $admin;
        $this->orderBy = $orderBy;
        $this->locale = $locale;
    }
}
