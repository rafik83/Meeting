<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class NomenclatureTagViewQuery implements Query
{
    /** @var array */
    public $tags;

    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    public function __construct(Event $event, array $tags, string $locale)
    {
        $this->tags = $tags;
        $this->event = $event;
        $this->locale = $locale;
    }
}
