<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Event;

class GenerateTaggedNomenclatureFilter
{
    /** @var null|Event */
    public $event;

    public function __construct(?Event $event = null)
    {
        $this->event = $event;
    }
}
