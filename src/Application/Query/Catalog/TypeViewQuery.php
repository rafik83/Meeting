<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class TypeViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var Type[] */
    public $visibleTypes;

    /** @var string */
    public $locale;

    /**
     * @param Event  $event
     * @param Type[] $visibleTypes
     * @param string $locale
     */
    public function __construct(Event $event, array $visibleTypes, $locale)
    {
        $this->event        = $event;
        $this->visibleTypes = $visibleTypes;
        $this->locale       = $locale;
    }
}
