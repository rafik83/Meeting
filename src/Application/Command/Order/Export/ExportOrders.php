<?php

namespace Proximum\Vimeet\Application\Command\Order\Export;

use Proximum\Vimeet\Application\Command\Command;

class ExportOrders implements Command
{
    /** @var int */
    public $eventId;

    /** @var string */
    public $emailToNotify;

    /** @var string */
    public $locale;

    /**
     * @param int    $eventId
     * @param string $emailToNotify
     * @param string $locale
     */
    public function __construct($eventId, $emailToNotify, $locale)
    {
        $this->eventId       = $eventId;
        $this->emailToNotify = $emailToNotify;
        $this->locale        = $locale;
    }
}
