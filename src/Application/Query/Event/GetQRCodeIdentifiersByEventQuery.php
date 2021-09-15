<?php

namespace Proximum\Vimeet\Application\Query\Event;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class GetQRCodeIdentifiersByEventQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var bool */
    public $getPreviousScan;

    public function __construct(
        Event $event,
        string $locale,
        bool $getPreviousScan = true
    ) {
        $this->event = $event;
        $this->locale = $locale;
        $this->getPreviousScan = $getPreviousScan;
    }
}
