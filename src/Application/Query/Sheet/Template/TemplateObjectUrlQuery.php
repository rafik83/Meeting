<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Template;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class TemplateObjectUrlQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var string */
    public $objectId;

    /** @var int|null index of element eith url, for collection object*/
    public $index;

    public function __construct(Sheet $sheet, Event $event, string $locale, string $objectId, ?int $index)
    {
        $this->sheet = $sheet;
        $this->event = $event;
        $this->locale = $locale;
        $this->objectId = $objectId;
        $this->index = $index;
    }
}
