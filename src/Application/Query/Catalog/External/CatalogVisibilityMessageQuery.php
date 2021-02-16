<?php

namespace Proximum\Vimeet\Application\Query\Catalog\External;

use Proximum\Vimeet\Domain\Model\Event;

class CatalogVisibilityMessageQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * CatalogVisibilityMessageQuery constructor.
     *
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(Event $event, string $locale)
    {
        $this->event  = $event;
        $this->locale = $locale;
    }
}
