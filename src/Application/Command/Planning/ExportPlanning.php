<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planning;

class ExportPlanning
{
    /** @var array */
    public $sheetIds;

    /** @var string */
    public $orderBy;

    /** @var string */
    public $emailToNotify;

    /** @var string */
    public $locale;

    /**
     * @param array  $sheetIds
     * @param string $orderBy
     * @param string $emailToNotify
     * @param string $locale
     */
    public function __construct(array $sheetIds, $orderBy, $emailToNotify, $locale)
    {
        $this->sheetIds      = $sheetIds;
        $this->orderBy       = $orderBy;
        $this->emailToNotify = $emailToNotify;
        $this->locale        = $locale;
    }
}
