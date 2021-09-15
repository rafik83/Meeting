<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Export;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class PrepareExport implements Command
{
    /** @var Event */
    public $event;

    /** @var Admin */
    public $admin;

    /** @var string */
    public $locale;

    public function __construct(Event $event, Admin $admin, string $locale)
    {
        $this->event  = $event;
        $this->admin  = $admin;
        $this->locale = $locale;
    }
}
