<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class HappeningParticipantExportViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /**
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(Event $event, string $locale)
    {
        $this->event = $event;
        $this->locale = $locale;
    }
}
