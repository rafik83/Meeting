<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Batch implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var array */
    public $ids;

    /** @var string */
    public $campaignTitle;

    public function __construct(Event $event, string $locale)
    {
        $this->event = $event;
        $this->locale = $locale;
    }
}
