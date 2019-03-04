<?php

namespace Proximum\Vimeet\Application\Command\Product\Export;

class ExportProducts
{
    /** @var int */
    public $eventId;

    /** @var string */
    public $emailToNotify;

    /** @var string */
    public $locale;

    public function __construct(int $eventId, string $emailToNotify, string $locale)
    {
        $this->eventId = $eventId;
        $this->emailToNotify = $emailToNotify;
        $this->locale = $locale;
    }
}
