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
    public $typeIds;

    /** @var string */
    public $orderBy;

    /** @var string */
    public $emailToNotify;

    /** @var string */
    public $locale;

    /**
     * @param array  $typeIds
     * @param string $orderBy
     * @param string $emailToNotify
     * @param string $locale
     */
    public function __construct(array $typeIds, $orderBy, $emailToNotify, $locale)
    {
        $this->typeIds       = $typeIds;
        $this->orderBy       = $orderBy;
        $this->emailToNotify = $emailToNotify;
        $this->locale        = $locale;
    }
}
