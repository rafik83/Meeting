<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class BatchPdf
{
    /** @var string */
    public $emailToNotify;

    /** @var string */
    public $locale;

    /** @var Event */
    public $event;

    /** @var array */
    public $sheetIds;

    /**
     * @param array  $sheetIds
     * @param string $emailToNotify
     * @param string $locale
     */
    public function __construct(array $sheetIds, string $emailToNotify, string $locale)
    {
        $this->emailToNotify = $emailToNotify;
        $this->locale        = $locale;
        $this->sheetIds      = $sheetIds;
    }
}
