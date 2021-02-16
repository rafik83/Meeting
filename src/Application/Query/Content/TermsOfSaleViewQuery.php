<?php

namespace Proximum\Vimeet\Application\Query\Content;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class TermsOfSaleViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    public function __construct(Event $event, Sheet $sheet, string $locale)
    {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->locale = $locale;
    }
}
