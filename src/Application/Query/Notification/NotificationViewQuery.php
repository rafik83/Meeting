<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Notification;

use Proximum\Vimeet\Domain\Model\Sheet;

class NotificationViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * NotificationViewQuery constructor.
     *
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
