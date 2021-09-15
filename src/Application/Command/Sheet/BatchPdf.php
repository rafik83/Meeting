<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;

class BatchPdf implements Command
{
    /** @var string */
    public $emailToNotify;

    /** @var string */
    public $locale;

    /** @var int */
    public $eventId;

    /** @var int[] */
    public $sheetIds;

    /** @var string */
    public $orderBy;

    /**
     * @param int    $eventId
     * @param array  $sheetIds
     * @param string $emailToNotify
     * @param string $locale
     * @param string $orderBy
     */
    public function __construct(int $eventId, array $sheetIds, string $emailToNotify, string $locale, string $orderBy)
    {
        $this->eventId       = $eventId;
        $this->emailToNotify = $emailToNotify;
        $this->locale        = $locale;
        $this->sheetIds      = $sheetIds;
        $this->orderBy       = $orderBy;
    }
}
