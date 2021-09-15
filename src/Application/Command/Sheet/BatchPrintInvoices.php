<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;

class BatchPrintInvoices implements Command
{
    /** @var string */
    public $emailToNotify;

    /** @var string */
    public $locale;

    /** @var int */
    public $eventId;

    /** @var int[] */
    public $sheetIds;

    /**
     * @param int    $eventId
     * @param int[]  $sheetIds
     * @param string $emailToNotify
     * @param string $locale
     */
    public function __construct(int $eventId, array $sheetIds, string $emailToNotify, string $locale)
    {
        $this->eventId = $eventId;
        $this->emailToNotify = $emailToNotify;
        $this->locale = $locale;
        $this->sheetIds = $sheetIds;
    }
}
