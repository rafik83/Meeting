<?php

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

    /** @var string */
    public $printOption;

    public function __construct(
        array $sheetIds,
        string $orderBy,
        string $emailToNotify,
        string $locale,
        string $printOption
    ) {
        $this->sheetIds = $sheetIds;
        $this->orderBy = $orderBy;
        $this->emailToNotify = $emailToNotify;
        $this->locale = $locale;
        $this->printOption = $printOption;
    }
}
