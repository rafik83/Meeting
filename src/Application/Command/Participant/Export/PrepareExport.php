<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant\Export;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class PrepareExport implements Command
{
    /** @var Event */
    public $event;

    /** @var array */
    public $filters;

    /** @var Admin */
    public $admin;

    /** @var string */
    public $locale;

    public function __construct(Event $event, array $filters, Admin $admin, string $locale)
    {
        $this->event = $event;
        $this->filters = $filters;
        $this->admin = $admin;
        $this->locale = $locale;
    }
}
