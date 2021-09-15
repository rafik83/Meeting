<?php

namespace Proximum\Vimeet\Application\Query\Visio;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Visio\VisioSettings;

class UpdateVisioSettingsViewQuery implements Query
{
    /** @var VisioSettings */
    public $visioSettings;
    /** @var Event */
    public $event;

    public function __construct(Event $event, VisioSettings $visioSettings)
    {
        $this->visioSettings = $visioSettings;
        $this->event = $event;
    }
}
