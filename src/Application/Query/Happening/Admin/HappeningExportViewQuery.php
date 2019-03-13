<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class HappeningExportViewQuery implements Query
{
    /** @var string */
    public $locale;

    /** @var Event */
    public $event;

    /**
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(Event $event, $locale)
    {
        $this->locale = $locale;
        $this->event = $event;
    }
}
