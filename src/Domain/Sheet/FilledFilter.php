<?php

namespace Proximum\Vimeet\Domain\Sheet;

class FilledFilter
{
    public const NOT_FILLED = 'not_filled';
    public const PARTLY_FILLED = 'partly_filled';
    public const FILLED = 'filled';

    public const FILLED_FILTERS = [
        self::NOT_FILLED,
        self::PARTLY_FILLED,
        self::FILLED,
    ];
}
