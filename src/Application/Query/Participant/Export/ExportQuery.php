<?php

namespace Proximum\Vimeet\Application\Query\Participant\Export;

use Proximum\Vimeet\Domain\Model\Event;

class ExportQuery
{
    /** @var Event */
    public $event;

    /** @var int[] */
    public $participantIds;

    /** @var string */
    public $locale;

    /**
     * @param Event  $event
     * @param array  $participantIds
     * @param string $locale
     */
    public function __construct(Event $event, array $participantIds, $locale)
    {
        $this->event = $event;
        $this->participantIds = $participantIds;
        $this->locale = $locale;
    }
}
