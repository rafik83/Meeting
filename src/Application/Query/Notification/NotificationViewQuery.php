<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
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
