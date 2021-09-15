<?php

namespace Proximum\Vimeet\Application\Command\Participant\Import;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class CreateMapping implements Command
{
    /** @var Event */
    public $event;

    /** @var array */
    public $mapping;

    /** @var string */
    public $title;

    public function __construct(
        Event $event,
        array $mapping,
        string $title = ''
    ) {
        $this->event = $event;
        $this->mapping = $mapping;
        $this->title = $title;
    }
}
