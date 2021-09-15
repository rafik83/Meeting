<?php

namespace Proximum\Vimeet\Domain\Planning;

class PlanningOrderedBy
{
    const ORDER_BY_SHEET_TITLE           = 'sheet_title';
    const ORDER_BY_PARTICIPANT_LAST_NAME = 'participant_last_name';
    const ORDER_BY_SPOT_REFERENCE        = 'spot_reference';

    /**
     * @return array
     */
    public static function getPlanningOrderByOptions(): array
    {
        return [
            self::ORDER_BY_SHEET_TITLE           => self::ORDER_BY_SHEET_TITLE,
            self::ORDER_BY_PARTICIPANT_LAST_NAME => self::ORDER_BY_PARTICIPANT_LAST_NAME,
            self::ORDER_BY_SPOT_REFERENCE        => self::ORDER_BY_SPOT_REFERENCE,
        ];
    }
}
