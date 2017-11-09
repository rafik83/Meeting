<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class BatchPdfJobCreator extends AbstractBatch
{
    /** @var string */
    public $locale;

    /** @var string */
    public $emailToNotify;

    /** @var array */
    public $sheetIds;

    /** @var Event */
    public $event;

    /**
     * @param Event  $event
     * @param array  $sheetIds
     * @param Admin  $admin
     * @param string $locale
     */
    public function __construct(Event $event, array $sheetIds, Admin $admin, string $locale)
    {
        $this->event         = $event;
        $this->emailToNotify = $admin->getEmail();
        $this->locale        = $locale;
        $this->sheetIds      = $sheetIds;
    }
}
