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

    /** @var bool */
    public $withBadge;

    public function __construct(
        array $sheetIds,
        string $orderBy,
        string $emailToNotify,
        string $locale,
        bool $withBadge = false
    ) {
        $this->sheetIds = $sheetIds;
        $this->orderBy = $orderBy;
        $this->emailToNotify = $emailToNotify;
        $this->locale = $locale;
        $this->withBadge = $withBadge;
    }
}
