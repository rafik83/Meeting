<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

class BatchPdf
{
    /** @var string */
    public $emailToNotify;

    /** @var string */
    public $locale;

    /** @var int */
    public $eventId;

    /** @var Int[] */
    public $sheetIds;

    /**
     * @param int    $eventId
     * @param array  $sheetIds
     * @param string $emailToNotify
     * @param string $locale
     */
    public function __construct(int $eventId, array $sheetIds, string $emailToNotify, string $locale)
    {
        $this->eventId       = $eventId;
        $this->emailToNotify = $emailToNotify;
        $this->locale        = $locale;
        $this->sheetIds      = $sheetIds;
    }
}
