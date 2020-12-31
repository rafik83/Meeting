<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class GetApiListQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var string */
    public $baseUrl;

    /** @var int[]|null */
    public $participantTypeIds;

    public function __construct(Event $event, string $locale, string $baseUrl, ?array $participantTypeIds)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->baseUrl = $baseUrl;
        $this->participantTypeIds = $participantTypeIds;
    }
}
