<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Planning;

class PlanningOrderedBy
{
    const ORDER_BY_SHEET_TITLE           = 'sheet_title';
    const ORDER_BY_PARTICIPANT_LAST_NAME = 'participant_last_name';

    /**
     * @return array
     */
    public static function getPlanningOrderByOptions()
    {
        return [
            self::ORDER_BY_SHEET_TITLE           => self::ORDER_BY_SHEET_TITLE,
            self::ORDER_BY_PARTICIPANT_LAST_NAME => self::ORDER_BY_PARTICIPANT_LAST_NAME,
        ];
    }
}
